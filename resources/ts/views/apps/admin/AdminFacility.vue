<script setup lang="ts">
import AddNewFacility from '@/views/apps/admin/form/AddNewFacilityForm.vue';
import type { FacilityProperties } from '@/views/apps/admin/types';
import { useFacilityListStore } from '@/views/apps/admin/useFacilitiesStore';
import { utils, writeFileXLSX } from 'xlsx';

// 👉 Store
const facilityListStore = useFacilityListStore()
const isDialogVisible = ref(false)
const facilityId = ref()
const searchQuery = ref('')
const rowPerPage = ref(5)
const currentPage = ref(1)
const totalPage = ref(1)
const facilitiesData = ref()
const formTitle = ref()
const responseData = ref()
const selectedFacility = ref()
const isAddNewUserDrawerVisible = ref(false)

// 👉 Filter facilities
const filterData = () => {
  const facilities = responseData.value
  const queryLower = searchQuery.value.toLowerCase()

  let filteredFacilities = facilities.filter((facility: { name: string; }) => ((facility.name.toLowerCase().includes(queryLower)))).reverse()

  totalPage.value = Math.ceil(filteredFacilities.length / rowPerPage.value) ? Math.ceil(filteredFacilities.length / rowPerPage.value) : 1

  if (rowPerPage.value) {
    const firstIndex = (currentPage.value - 1) * rowPerPage.value
    const lastIndex = rowPerPage.value * currentPage.value

    filteredFacilities = filteredFacilities.slice(firstIndex, lastIndex)
  }

  facilitiesData.value = {
    facility: filteredFacilities,
  }
}

// 👉 Fetching facilities
const fetchFacilities = async () => {
  responseData.value = ''
  try {
    const response = await facilityListStore.fetchFacility();
    responseData.value = response.data.data;
    console.log(responseData.value)
    filterData();
  } catch (error) {
    console.error(error);
  }
};

watchEffect(fetchFacilities)

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPage.value)
    currentPage.value = totalPage.value
})

const resolveUserStatusVariant = (stat: boolean) => {
  if (stat)
    return 'success'

  return 'secondary'

  /* if (stat === 'pending')
    return 'warning'
  if (stat === '1')
    return 'success'
  if (stat === 'inactive')
    return 'secondary'

  return 'primary' */
}

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPage.value)
    currentPage.value = totalPage.value
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  if (facilitiesData.value) {
    const firstIndex = facilitiesData.value.facility.length ? ((currentPage.value - 1) * rowPerPage.value) + 1 : 0
    const lastIndex = facilitiesData.value.facility.length + ((currentPage.value - 1) * rowPerPage.value)

    return `Showing ${firstIndex} to ${lastIndex} of ${responseData.value.length} entries`
  }
})

// 👉 Add new Facility
const addNewFacility = (facilityData: FacilityProperties) => {
  facilityListStore.addFacility(facilityData)

  // refetch Facility
  fetchFacilities()
}

// 👉 Delete Facility
const catchId = (e: any) => {
  facilityId.value = e
  isDialogVisible.value = true
}

const deleteFacility = async (e: boolean) => {
  if (e) {
    await facilityListStore.deleteFacility(facilityId.value)
    isDialogVisible.value = false
    await fetchFacilities()
  }
  else {
    facilityId.value = ''
    isDialogVisible.value = false
  }
}

// 👉 Update Facility
const updateFacility = async (updateData: FacilityProperties) => {
  await facilityListStore.updateFacility(updateData, selectedFacility.value.id);

  // refetch Facility
  await fetchFacilities();
}

const formEvent = (e: any) => {
  if (e) {
    isAddNewUserDrawerVisible.value = true

    selectedFacility.value = facilitiesData.value.facility.filter((facility: { id: any; }) => facility.id === e)[0]

    formTitle.value = 'Update'
  }
  else {
    formTitle.value = 'Add'
    isAddNewUserDrawerVisible.value = true
    selectedFacility.value = ''
  }
}

function exportFile() {
  const facilitiesWithoutId = responseData.value.map(({ id, limit_user, ...facility }) => facility)
  facilitiesWithoutId.forEach((item: { status: string | number; }) => {
    if (item.status === 1) {
      item.status = "active";
    } else if (item.status === 0) {
      item.status = "disable";
    }
  });
  const ws = utils.json_to_sheet(facilitiesWithoutId)
  const wb = utils.book_new()

  utils.book_append_sheet(wb, ws, 'Data')
  writeFileXLSX(wb, 'facilityList.xlsx')
}
</script>

<template>
  <section v-if="facilitiesData">
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardText class="d-flex flex-wrap py-4 gap-4">
            <div class="me-3" style="width: 80px;">
              <VSelect v-model="rowPerPage" density="compact" variant="outlined" :items="[5, 10, 15]"
                @update:model-value="filterData" />
            </div>

            <VSpacer />

            <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
              <!-- 👉 Search  -->
              <div style="width: 10rem;">
                <VTextField v-model="searchQuery" placeholder="Search" density="compact" @keyup="filterData" />
              </div>

              <!-- 👉 Export button -->
              <VBtn variant="tonal" color="primary" prepend-icon="tabler-screen-share" @click="exportFile">
                Export
              </VBtn>

              <!-- 👉 Add user button -->
              <VBtn prepend-icon="tabler-plus" @click="formEvent('')">
                Add Facility
              </VBtn>
            </div>
          </VCardText>

          <VDivider />

          <VTable class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col">
                  NAME
                </th>
                <th scope="col">
                  Maximum Time (Minutes)
                </th>
                <!--  <th scope="col">
                  Maximum Booking
                </th> -->
                <th scope="col">
                  DESCRIPTION
                </th>
                <th scope="col">
                  STATUS
                </th>
                <th scope="col">
                  ACTIONS
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr v-for="facility in facilitiesData.facility" :key="facility.id" style="height: 3.75rem;">
                <!-- 👉 Name -->
                <td>
                  <div class="d-flex align-center">
                    {{ facility.name }}
                  </div>
                </td>

                <td>
                  <div class="d-flex align-center">
                    {{ facility.maximum_time }}
                  </div>
                </td>

                <!-- <td>
                  <div class="d-flex align-center">
                    {{ facility.limit_user }}
                  </div>
                </td> -->


                <!-- 👉 Description -->
                <td>
                  {{ facility.description }}
                </td>

                <!-- 👉 Status -->
                <td v-if="facility.status === 1">
                  <VChip label :color="resolveUserStatusVariant(facility.status)" size="small" class="text-capitalize">
                    Active
                  </VChip>
                </td>
                <td v-if="facility.status === 0">
                  <VChip label :color="resolveUserStatusVariant(facility.status)" size="small" class="text-capitalize">
                    Disable
                  </VChip>
                </td>

                <!-- 👉 Actions -->
                <td class="text-center" style="width: 5rem;">
                  <VBtn icon size="x-small" color="default" variant="text">
                    <VIcon size="22" icon="tabler-edit" @click="formEvent(facility.id)" />
                  </VBtn>

                  <VBtn icon size="x-small" color="default" variant="text">
                    <VIcon size="22" icon="tabler-trash" @click="catchId(facility.id)" />
                  </VBtn>
                </td>
              </tr>
            </tbody>

            <!-- 👉 table footer  -->

            <!--
                          <tfoot v-show="!facilities.length">
                          <tr>
                          <td
                          colspan="7"
                          class="text-center"
                          >
                          No data available
                          </td>
                          </tr>
                          </tfoot>
                        -->
          </VTable>

          <VDivider />

          <VCardText class="d-flex align-center flex-wrap justify-space-between gap-4 py-3 px-5">
            <span class="text-sm text-disabled">
              {{ paginationData }}
            </span>

            <VPagination v-model="currentPage" size="small" :total-visible="5" :length="totalPage" @click="filterData" />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- 👉 Add New User -->
    <AddNewFacility v-model:isDrawerOpen="isAddNewUserDrawerVisible" :title="formTitle" :data="selectedFacility"
      @facility-data="addNewFacility" @update-data="updateFacility" />
  </section>
  <VDialog v-model="isDialogVisible" persistent class="v-dialog-sm">
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" />

    <!-- Dialog Content -->
    <VCard title="Comfirm Delete Facility?">
      <VCardText class="d-flex justify-end gap-3 flex-wrap">
        <VBtn color="secondary" variant="tonal" @click="deleteFacility(false)">
          Cancel
        </VBtn>
        <VBtn color="error" @click="deleteFacility(true)">
          Delete
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style lang="scss">
.app-user-search-filter {
  inline-size: 31.6rem;
}

.text-capitalize {
  text-transform: capitalize;
}

.user-list-name:not(:hover) {
  color: rgba(var(--v-theme-on-background), var(--v-high-emphasis-opacity));
}
</style>
