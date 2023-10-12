<script setup lang="ts">
import { read, utils, writeFileXLSX } from 'xlsx'
import ServiceRequestForm from '../workflow/form/ServiceRequestForm.vue'
import { useFacilityListStore } from '@/views/apps/admin/useFacilitiesStore'
import type { ServiceRequest } from '@/views/apps/admin/types'
import moment from 'moment'
// 👉 Store
const facilityListStore = useFacilityListStore()
const isDialogVisible = ref(false)
const textDialog = ref(false)
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
const textDetails = ref()
const formRole = ref()



const filterData = () => {
  const facilities = responseData.value
  const queryLower = searchQuery.value.toLowerCase()

  let filteredFacilities = facilities.filter(facility => ((facility.number.toLowerCase().includes(queryLower)))).reverse()

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
const fetchFacilities = () => {
  facilityListStore.fetchAdminService().then(response => {
    responseData.value = response.data.data
    console.log(responseData.value)
    filterData()
  }).catch(error => {
    console.error(error)
  })
}

watchEffect(fetchFacilities)

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPage.value)
    currentPage.value = totalPage.value
})

const resolveUserStatusVariant = (stat: string) => {
  if (stat === 'New')
    return 'primary'
  if (stat === 'Work In Progress' || stat === 'Pending In Replacement Part')
    return 'warning'
  else
    return 'secondary'
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
const catchId = e => {
  facilityId.value = e
  isDialogVisible.value = true
}

const deleteFacility = async (e: boolean) => {
  if (e) {
    await facilityListStore.deleteComplaint(facilityId.value)
    isDialogVisible.value = false
    await fetchFacilities()
  }
  else {
    facilityId.value = ''
    isDialogVisible.value = false
  }
}

// 👉 Update Facility
const updateFacility = async (updateData: ServiceRequest) => {
  await facilityListStore.updateServiceRequest(updateData, selectedFacility.value.id);

  //console.log(message.value.data.message)

  // refetch Facility
  await fetchFacilities();

  //isSnackbarVisible.value = true
}
const formEvent = async (e) => {
  if (e) {
    await fetchFacilities()
    isAddNewUserDrawerVisible.value = true
    selectedFacility.value = facilitiesData.value.facility.filter(facility => facility.id === e)[0]
    formTitle.value = 'Update'
    formRole.value = 'admin'
  }
  else {
    formTitle.value = 'Add'
    isAddNewUserDrawerVisible.value = true
    selectedFacility.value = ''
  }
}



function exportFile() {
  const facilitiesWithoutId = responseData.value.map(({ id, ...facility }) => facility)
  const ws = utils.json_to_sheet(facilitiesWithoutId)
  const wb = utils.book_new()

  utils.book_append_sheet(wb, ws, 'Data')
  writeFileXLSX(wb, 'complaintList.xlsx')
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
              <!--
                <VBtn
                prepend-icon="tabler-plus"
                @click="formEvent('')"
                    >
                    Add Facility
                    </VBtn>
                  -->
            </div>
          </VCardText>

          <VDivider />

          <VTable class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col">
                  #Number1
                </th>
                <th scope="col">
                  USER CREATED
                </th>
                <th scope="col">
                  DATE CREATED
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
                    {{ facility.number }}
                  </div>
                </td>

                <td>
                  <div class="d-flex align-center">
                    {{ facility.user_created.name }}
                  </div>
                </td>

                <!-- 👉 Description -->
                <td>
                  {{ moment(facility.created).format("DD/MM/YYYY h:mm A") }}
                </td>

                <!-- 👉 Description -->
                <!--  <td v-if="facility.details.length > 25" @click="textEvent(facility.details)">
                  <VTooltip activator="parent" location="center">
                    Click See More
                  </VTooltip>
                  <span class="d-inline-block text-truncate" style="max-width: 200px;">
                    {{ facility.details }}
                  </span>
                </td>
                <td v-else>
                  {{ facility.details }}
                </td> -->

                <!-- 👉 Status -->
                <td>
                  <VChip label :color="resolveUserStatusVariant(facility.status)" size="small" class="text-capitalize">
                    {{ facility.status }}
                  </VChip>
                </td>

                <!-- 👉 Actions -->
                <td class="text-center" style="width: 5rem;">
                  <!--  <VBtn icon size="x-small" color="default" variant="text">
                    <VIcon size="22" icon="tabler-edit" @click="formEvent(facility.id)" />
                  </VBtn> -->
                  <VBtn icon size="x-small" color="default" variant="text">
                    <VIcon size="22" icon="tabler-edit" @click="formEvent(facility.id)" />
                  </VBtn>
                  <!--  <VBtn icon size="x-small" color="default" variant="text">
                    <VIcon size="22" icon="tabler-trash" @click="catchId(facility.id)" />
                  </VBtn> -->
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
    <ServiceRequestForm v-model:isDrawerOpen="isAddNewUserDrawerVisible" :title="formTitle" :role='formRole'
      :data="selectedFacility" @facility-data="addNewFacility" @update-data="updateFacility" />
  </section>
  <VDialog v-model="isDialogVisible" persistent class="v-dialog-sm">
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" />

    <!-- Dialog Content -->
    <VCard title="Comfirm Delete Complaint?">
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
  <VDialog v-model="textDialog" persistent class="v-dialog-sm">
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="textDialog = !textDialog" />

    <!-- Dialog Content -->
    <VCard title="Details">
      <VCardText>
        {{ textDetails }}
      </VCardText>

      <VCardText class="d-flex justify-end gap-3 flex-wrap">
        <VBtn color="primary" variant="tonal" @click="textDialog = !textDialog">
          Close
        </VBtn>
        <!--
              <VBtn
              color="error"
              @click="deleteFacility(true)"
              >
              Delete
              </VBtn>
            -->
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
