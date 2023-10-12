<script setup lang="ts">
import AddNewFacility from '@/views/apps/admin/form/AddNewFacilityForm.vue';
import WorkOrderForm from './form/WorkOrderForm.vue';
import DetailsPanel from './DetailsPanel.vue';
import type { WorkOrder } from '@/views/apps/admin/types';
import { useFacilityListStore } from '@/views/apps/admin/useFacilitiesStore';
import { utils, writeFileXLSX } from 'xlsx';
import moment from 'moment';

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
const detail = ref()
const displayPanel = ref(false)
const loader = ref(false)
const isShowDetails = ref(false)
const formRole = ref()

// 👉 Filter facilities
const filterData = () => {
  const facilities = responseData.value
  const queryLower = searchQuery.value.toLowerCase()

  let filteredFacilities = facilities.filter((facility: { name: string; }) => ((facility.number.toLowerCase().includes(queryLower)))).reverse()

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
    const response = await facilityListStore.fetchUserWorkOrder();
    responseData.value = response.data.data;
    console.log(responseData.value)
    loader.value = false
    filterData();
  } catch (error) {
    loader.value = false
    console.error(error);
  }
};

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



const getData = async () => {
  console.log('masuk')
  await fetchFacilities()
} 

// 👉 Add new Facility
const addNewFacility = async (facilityData: WorkOrder) => {
  loader.value = true
  await facilityListStore.addServiceRequest(facilityData)
  // refetch Facility
  await fetchFacilities()
}

// 👉 Delete Facility
const catchId = (e: any) => {
  facilityId.value = e
  isDialogVisible.value = true
}

const deleteFacility = async (e: boolean) => {
  if (e) {
    loader.value = true
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
const updateFacility = async () => {
  loader.value = true
  console.log('masuk')
  await fetchFacilities();
}

const formEvent = (e: any) => {
  formRole.value = 'user'
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

const showDetails = (e: any) => {
  detail.value = facilitiesData.value.facility.filter((facility: { id: any; }) => facility.id === e)[0]
  //isShowDetails.value = true
  console.log(detail.value)
  displayPanel.value = true
}


const downloadFile = async (e, g) => {
  const downloadData = {
    id: e,
  }
  console.log(g)

  const { data } = await facilityListStore.downloadFile(downloadData);
  const mimeTypes: { [key: string]: string } = {
    '.jpeg': 'image/jpeg',
    '.jpg': 'image/jpeg',
    '.png': 'image/png',
    '.pdf': 'application/pdf',
    '.xls': 'application/vnd.ms-excel',
    '.mp4': 'video/mp4'
    // ... Add more as needed
  };
  const extension = `.${g.split('.').pop()}`;
  const mimeType = mimeTypes[extension];
  const url = URL.createObjectURL(
    new Blob([data], {
      type: mimeType,
    })
  )
  const link = document.createElement('a')
  link.href = url
  link.setAttribute('download', g)
  document.body.appendChild(link)
  link.click()


}
</script>

<template>
  <section v-if="facilitiesData">
    <VRow>
      <VCol cols="12">
        <VCard>
          <v-overlay :model-value="loader" class="align-center justify-center">
            <v-progress-circular color="primary" indeterminate size="38"></v-progress-circular>
          </v-overlay>
          <VCardText class="d-flex flex-wrap py-4 gap-4">
            <div class="me-3" style="width: 80px;">
              <VSelect v-model="rowPerPage" density="compact" variant="outlined" :items="[5, 10, 15]"
                @update:model-value="filterData" />
            </div>
            <VSpacer />

            <div class="app-user-search-filter d-flex  flex-row-reverse align-center flex-wrap gap-4">
              <!-- 👉 Search  -->
              <div style="width: 10rem;">
                <VTextField v-model="searchQuery" placeholder="Search" density="compact" @keyup="filterData" />
              </div>



              <!-- 👉 Add user button -->
              <VBtn prepend-icon="tabler-plus" @click="formEvent('')">
                New Work Order
              </VBtn>
            </div>
          </VCardText>

          <VDivider />

          <VTable class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col">
                  REQUEST NO.
                </th>
                <th scope="col">
                  CREATED
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
                    <a @click="showDetails(facility.id)" class="cursor">{{ facility.number }}</a>
                  </div>
                </td>

                <td>
                  <div class="d-flex align-center">
                    {{ moment(facility.created).format("DD/MM/YYYY h:mm A") }}
                  </div>
                </td>

                <!-- 👉 Status -->
                <td>
                  <VChip label :color="resolveUserStatusVariant(facility.status)" size="small" class="text-capitalize">
                    {{ facility.status }}
                  </VChip>
                </td>

                <!-- 👉 Actions -->
                <td class="text-center" style="width: 5rem;" v-if="facility.status === 'New'">
                  <VBtn icon size="x-small" color="default" variant="text">
                    <VIcon size="22" icon="tabler-edit" @click="formEvent(facility.id)" />
                  </VBtn>

                  <VBtn icon size="x-small" color="default" variant="text">
                    <VIcon size="22" icon="tabler-trash" @click="catchId(facility.id)" />
                  </VBtn>
                </td>
              </tr>
            </tbody>

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
    <WorkOrderForm v-model:isDrawerOpen="isAddNewUserDrawerVisible" :title="formTitle" :data="selectedFacility"
      :role="formRole" @facility-data="addNewFacility" @update-data="updateFacility" @fetch-data="getData" />
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

  <VDialog v-model="isShowDetails" persistent class="v-dialog-xl">
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="isShowDetails = !isShowDetails" />

    <!-- Dialog Content -->
    <VCard title="Details">
      <section cols="12">
        <VRow>
          <VCol cols="12" md="9">
            <VCard>
              <!-- SECTION Header -->
              <VCardText class="d-flex flex-wrap justify-space-between flex-column flex-sm-row print-row">
                <!-- 👉 Left Content -->
                <div class="ma-sm-4">
                  <div class="d-flex align-center mb-6">
                    <!-- 👉 Title -->
                    <h6 class="font-weight-bold text-xl">
                      {{ selectedFacility.type }}
                    </h6>
                  </div>
                  <!-- 👉 Address -->
                  <p class="mb-0">
                    <span>Room Type: </span>
                    <span class="font-weight-semibold"> {{ selectedFacility.room_type }} </span>
                  </p>
                  <p class="mb-0">
                    <span>W/O Type: </span>
                    <span class="font-weight-semibold"> {{ selectedFacility.WO_type }} </span>
                  </p>
                  <p class="mb-0">
                    <span>Location: </span>
                    <span class="font-weight-semibold"> {{ selectedFacility.location }} </span>
                  </p>
                  <p class="mb-0">
                    <span>Department: </span>
                    <span class="font-weight-semibold"> {{ selectedFacility.company }} </span>
                  </p>
                  <p class="mb-0">
                    <span>Contact: </span>
                    <span class="font-weight-semibold"> +06 {{ selectedFacility.phone }} </span>
                  </p>
                </div>
                <!-- 👉 Right Content -->
                <div class="mt-4 ma-sm-4">
                  <!-- 👉 Invoice ID -->
                  <h6 class="font-weight-medium text-xl mb-6">
                    {{ selectedFacility.number }}
                  </h6>

                  <!-- 👉 Issue Date -->
                  <p class="mb-2">
                    <span>Date Issued: </span>
                    <span class="font-weight-semibold"> {{ moment(selectedFacility.created).format("DD/MM/YYYY h:mm A")
                    }}</span>
                  </p>

                  <!-- 👉 Due Date -->
                  <p class="mb-2">
                    <span>Due Date: </span>
                    <span class="font-weight-semibold"></span>
                  </p>
                </div>
              </VCardText>
              <!-- !SECTION -->
              <VDivider />
              <VCardText class="d-flex justify-space-between flex-wrap flex-column flex-sm-row print-row">
                <VCol cols="12">
                  <div class="d-flex align-center mb-6">
                    <!-- 👉 Title -->
                    <h6 class="font-weight-bold text-sm">
                      LIST ITEM
                    </h6>
                  </div>
                  <VRow>
                    <VCol cols="2" v-for="item in selectedFacility.component">
                      {{ item }}
                    </VCol>
                  </VRow>
                </VCol>
              </VCardText>
              <VDivider />
              <VCardText class="d-flex justify-space-between flex-wrap flex-column flex-sm-row print-row">
                <div class="ma-sm-4">
                  <h6 class="text-sm font-weight-semibold mb-3">
                    Contact Reason:
                  </h6>
                  <p class="mb-1">
                    {{ selectedFacility.contact_reason }}
                  </p>
                </div>
              </VCardText>
              <!-- 👉 Table -->
              <VDivider />
              <VCardText>
                <div class="d-flex mx-sm-4">
                  <h6 class="text-sm font-weight-semibold me-1">
                    Report:
                  </h6>
                  <span>{{ selectedFacility.report }}</span>
                </div>
              </VCardText>
              <VDivider />
              <VCardText class="d-flex justify-space-between flex-wrap flex-column flex-sm-row print-row">
                <VCol cols="12">
                  <div class="d-flex align-center mb-6">
                    <!-- 👉 Title -->
                    <h6 class="font-weight-bold text-sm">
                      ATTACHMENT FILE
                    </h6>
                  </div>
                  <VRow>
                    <VCol cols="3" v-for="item in selectedFacility.attachment_file">
                      
                      <a @click="downloadFile(item.id, item.file_name)"> <VIcon size="22" icon="tabler-paperclip"/> {{ item.file_name }} </a>
                    </VCol>
                  </VRow>
                </VCol>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </section>
      <VCardText class="d-flex justify-end gap-3 flex-wrap">
        <VBtn color="secondary" variant="tonal" @click="isShowDetails = !isShowDetails">
          Close
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>

  <DetailsPanel  v-model:isDrawerOpen="displayPanel" title="test" :data="detail" />
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
