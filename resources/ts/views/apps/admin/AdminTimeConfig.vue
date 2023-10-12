<script setup lang="ts">
import AddNewFacility from '@/views/apps/admin/form/AddNewFacilityForm.vue';
import TimeConfig from '@/views/apps/admin/form/TimeConfigForm.vue';
import type { ConfigTime } from '@/views/apps/admin/types';
import { useFacilityListStore } from '@/views/apps/admin/useFacilitiesStore';
import { utils, writeFileXLSX } from 'xlsx';
import moment from 'moment'

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

  let filteredFacilities = facilities.filter((facility: { day_name: string; }) => ((facility.day_name.toLowerCase().includes(queryLower)))).reverse()

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
    const response = await facilityListStore.fetchTimeConfig();
    facilitiesData.value = response.data.data;
    console.log(facilitiesData.value)
    //filterData();
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

    return `Showing ${firstIndex} to ${lastIndex} of ${facilitiesData.value.length} entries`
  }
})

// 👉 Add new Facility
const addNewFacility = (facilityData: ConfigTime) => {
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
    await facilityListStore.deleteConfig(facilityId.value)
    isDialogVisible.value = false
    await fetchFacilities()
  }
  else {
    facilityId.value = ''
    isDialogVisible.value = false
  }
}

// 👉 Update Facility
const updateFacility = async (updateData: ConfigTime) => {
  await facilityListStore.updateTimeConfig(updateData, selectedFacility.value.id);

  // refetch Facility
  await fetchFacilities();
}

const formEvent = (e: any) => {
  if (e) {
    isAddNewUserDrawerVisible.value = true

    selectedFacility.value = facilitiesData.value.filter((facility: { id: any; }) => facility.id === e)[0]
    console.log(selectedFacility.value)
    formTitle.value = 'Update'
  }
  else {
    formTitle.value = 'Add'
    isAddNewUserDrawerVisible.value = true
    selectedFacility.value = ''
  }
}

function exportFile() {
  const facilitiesWithoutId = facilitiesData.value.map(({ id, limit_user, ...facility }) => facility)
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
          <VTable class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col">
                  Day
                </th>
                <th scope="col">
                  Time
                </th>
                <!--  <th scope="col">
                  Maximum Booking
                </th> -->
                <th scope="col">
                  List Facility
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
              <tr v-for="facility in facilitiesData" :key="facility.id" style="height: 3.75rem;">
                <!-- 👉 Name -->
                <td>
                  <div class="d-flex align-center">
                    {{ facility.config_value.day_name }}
                    
                  </div>
                </td>

                <td>
                  <div class="d-flex align-center">
                    Start - {{ moment(facility.config_value.start, "HH:mm:ss").format('h:mm A') }}<br />
                    End - {{ moment(facility.config_value.end, "HH:mm:ss").format('h:mm A') }}
                  </div>
                </td>

                <!-- <td>
                  <div class="d-flex align-center">
                    {{ facility.limit_user }}
                  </div>
                </td> -->


                <!-- 👉 Description -->
                <td>
                  <div v-for="item in facility.facility">
                    {{ item }}
                  </div>
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
            <!--  <span class="text-sm text-disabled">
              {{ paginationData }}
            </span> -->

            <!-- <VPagination v-model="currentPage" size="small" :total-visible="5" :length="totalPage" @click="filterData" /> -->
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- 👉 Add New User -->
    <TimeConfig v-model:isDrawerOpen="isAddNewUserDrawerVisible" :title="formTitle" :data="selectedFacility"
      @facility-data="addNewFacility" @update-data="updateFacility" />
  </section>
  <VDialog v-model="isDialogVisible" persistent class="v-dialog-sm">
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" />

    <!-- Dialog Content -->
    <VCard title="Comfirm Delete Config?">
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
