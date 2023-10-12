<script setup lang="ts">
import { useFacilityListStore } from '@/views/apps/admin/useFacilitiesStore';
import type { BookingProperties } from '@/views/apps/booking/types';
import { filterDataTest } from '@/views/apps/main/filter';
import noBookingImg from '@images/pages/no-booking-grey.png';
import { requiredValidator } from '@validators';
import moment from 'moment';
import { VForm } from 'vuetify/components';
import AddNewBookingForm from './form/AddNewBookingForm.vue';

// 👉 Store
const bookingListStore = useFacilityListStore()
const isDialogVisible = ref(false)
const bookingId = ref()
const searchQuery = ref('')
const rowPerPage = ref(5)
const currentPage = ref(1)
const totalPage = ref(1)
const facilitiesData = ref()
const formTitle = ref()
const responseData = ref()
const selectedFacility = ref()
const isAddNewUserDrawerVisible = ref(false)
const isFormValid = ref(false)
const refForm = ref<VForm>()
const remark = ref()
const testData = ref()
const noticeDialog = ref(false)
const loader = ref(true)


// 👉 Filter facilities
const filterData = () => {
  const facilities = responseData.value
  const queryLower = searchQuery.value.toLowerCase()
  let filteredFacilities = facilities.filter((facility: {
    facility:{name:any};
  }) => ((facility.facility.name.toLowerCase().includes(queryLower)))).reverse()
  totalPage.value = Math.ceil(filteredFacilities.length / rowPerPage.value) ? Math.ceil(filteredFacilities.length / rowPerPage.value) : 1
  if (rowPerPage.value) {
    const firstIndex = (currentPage.value - 1) * rowPerPage.value
    const lastIndex = rowPerPage.value * currentPage.value
    filteredFacilities = filteredFacilities.slice(firstIndex, lastIndex)
  }

  facilitiesData.value = {
    facility: filteredFacilities,
  }
  //console.log(facilitiesData)
}

const testFilter = () => {
  const datafilter = 'booking'
  testData.value = filterDataTest(responseData.value, searchQuery.value, currentPage.value, totalPage.value, rowPerPage.value, datafilter)
  //console.log(testData)
  //console.log(facilitiesData)
}
// 👉 Fetching facilities
const fetchBookingList = async () => {
  responseData.value = ''
  try {
    const response = await bookingListStore.fetchBookingUser();
    responseData.value = response.data.data;
    console.log(responseData.value)
    filterData();
    testFilter();
    loader.value = false
  } catch (error) {
    loader.value = false
    console.error(error);
  }
};

watchEffect(fetchBookingList)

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPage.value)
    currentPage.value = totalPage.value
})

const resolveUserStatusVariant = (stat: string) => {
  if (stat === 'Completed')
    return 'secondary'
  if (stat === 'Active')
    return 'success'

  return 'warning'
}


// 👉 Computing pagination data
const paginationData = computed(() => {
  if (facilitiesData.value) {
    const firstIndex = facilitiesData.value.facility.length ? ((currentPage.value - 1) * rowPerPage.value) + 1 : 0
    const lastIndex = facilitiesData.value.facility.length + ((currentPage.value - 1) * rowPerPage.value)

    return `Showing ${firstIndex} to ${lastIndex} of ${responseData.value.length} entries`
  }
})

// 👉 Add new Facility
const AddNewBooking = async (bookingData: BookingProperties) => {
  loader.value = true
  await bookingListStore.addBooking(bookingData)

  // refetch Facility
  await fetchBookingList()
}

// 👉 Delete Facility
const catchId = (e: any) => {
  selectedFacility.value = facilitiesData.value.facility.filter((facility: { id: any; }) => facility.id === e)[0]
  const today = moment().format('YYYY-MM-DD');
  const startDay = moment(selectedFacility.value.start_time, 'YYYY-MM-DD')
  const diffDate = startDay.diff(today, 'days')
  if (diffDate >= 1) {
    isDialogVisible.value = true
  } else {
    noticeDialog.value = true
  }
 
  //console.log(selectedFacility.value.user.id)
}

const deleteBooking = async (updateData: BookingProperties) => {
  loader.value = true
  await bookingListStore.deleteBooking(updateData, selectedFacility.value.id)
  await fetchBookingList();  
  isDialogVisible.value = false

}

// 👉 Update Facility
const updateBooking = async (updateData: BookingProperties) => {
  loader.value = true
  await bookingListStore.updateBooking(updateData, selectedFacility.value.id);

  // refetch Facility
  await fetchBookingList();
}

const formEvent = (e: any) => {
  if (e) {
    isAddNewUserDrawerVisible.value = true

    selectedFacility.value = facilitiesData.value.facility.filter((facility: { id: any; }) => facility.id === e)[0]

    formTitle.value = 'Update'
  }
  else {
    formTitle.value = 'Create'
    isAddNewUserDrawerVisible.value = true
    selectedFacility.value = ''
  }
}


const submitForm = () => {
  refForm.value?.validate()
    .then(async ({ valid }) => {
      if (valid) {
        const formData = {
          user_id: selectedFacility.value.user.id,
          item_id: selectedFacility.value.facility.id,
          status: 'Cancel',
          remarks: remark.value,
          start_time: selectedFacility.value.start_time,
          end_time: selectedFacility.value.end_time
        }

        await deleteBooking(formData)
        refForm.value?.reset()
        refForm.value?.resetValidation()
      }
    })
}

onMounted(() => {
  loader.value = true
})

</script>

<template>
  <section v-if="testData">
    <VRow>
      <VCol cols="12">
        <VCard>
          <v-overlay
            :model-value="loader"
            class="align-center justify-center"
          >
            <v-progress-circular
              color="primary"
              indeterminate
              size="38"
            ></v-progress-circular>
          </v-overlay>
          <VCardText class="d-flex flex-wrap py-4 gap-4">
            <div class="me-3" style="width: 80px;">
              <VSelect v-model="rowPerPage" density="compact" variant="outlined" :items="[5, 10, 15]"
                @update:model-value="testFilter" />
            </div>

            <VSpacer />

            <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
              <!-- 👉 Search  -->
              <div style="width: 18rem;">
                <VTextField v-model="searchQuery" placeholder="Search" density="compact" @keyup="testFilter" />
              </div>

              <!-- 👉 Add user button -->
              <VBtn prepend-icon="tabler-plus" @click="formEvent('')">
                New Booking
              </VBtn>
            </div>
          </VCardText>

          <VDivider />

          <VTable class="text-no-wrap" v-if="testData.facility.length > 0">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col">
                  FACILITY
                </th>
                <th scope="col">
                  START DATE
                </th>
                <th scope="col">
                  END DATE
                </th>
                <th scope="col">
                  STATUS
                </th>
                <th scope="col">
                  REMARKS
                </th>
                <th scope="col">
                  ACTIONS
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr v-for="facility in testData.facility" :key="facility.id" style="height: 3.75rem;">
                <!-- 👉 Name -->
                <td>
                  <div class="d-flex align-center">
                    {{ facility.facility.name }}
                  </div>
                </td>

                <!-- 👉 Description -->
                <td>
                  <div class="d-flex align-center">
                    {{ moment(facility.start_time).format("DD/MM/YYYY h:mm A") }}
                  </div>
                </td>

                <!-- 👉 Description -->
                <td>
                  <div class="d-flex align-center">
                    {{ moment(facility.end_time).format("DD/MM/YYYY h:mm A") }}
                  </div>
                </td>

                <!-- 👉 Description -->
                <td>
                  <VChip label :color="resolveUserStatusVariant(facility.status)" size="small" class="text-capitalize">
                    {{ facility.status }}
                  </VChip>
                </td>
                <!-- 👉 Description -->
                <td>
                  {{ facility.remarks }}
                </td>

                <!-- 👉 Actions -->
                <td class="text-center" style="width: 5rem;">
                  <div v-if="facility.status == 'Active'">
                    <VBtn icon size="x-small" color="default" variant="text">
                      <VIcon size="22" icon="tabler-edit" @click="formEvent(facility.id)" />
                    </VBtn>

                    <VBtn icon size="x-small" color="default" variant="text">
                      <VIcon size="22" icon="tabler-circle-x" color="error" @click="catchId(facility.id)" />
                    </VBtn>
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
          <div v-else class="misc-wrapper">

            <!-- 👉 Image -->
            <div class="misc-avatar w-100 text-center" style="padding-top: 20px;">
              <VImg :src="noBookingImg" alt="No booking found" :max-width="300" class="mx-auto" />
            </div>

            <div class="misc-center-content text-center mb-12">
              <p>You have no booking yet.</p>
              <VBtn @click="formEvent('')">
                Book Now!
              </VBtn>
            </div>
          </div>

          <VDivider />

          <VCardText class="d-flex align-center flex-wrap justify-space-between gap-4 py-3 px-5">
            <span class="text-sm text-disabled">
              {{ paginationData }}
            </span>

            <VPagination v-model="currentPage" size="small" :total-visible="5" :length="totalPage" @click="testFilter" />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- 👉 Add New User -->
    <AddNewBookingForm v-model:isDrawerOpen="isAddNewUserDrawerVisible" :title="formTitle" :data="selectedFacility"
      @facility-data="AddNewBooking" @update-data="updateBooking" />
  </section>
  <VDialog v-model="isDialogVisible" persistent class="v-dialog-sm">
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" />

    <!-- Dialog Content -->
    <VForm ref="refForm" v-model="isFormValid" @submit.prevent="submitForm">
      <VCard title="Are you sure to cancel this booking?">
        <VCardText>
          <VRow>
            <VCol>
              <VTextarea v-model="remark" label="Remarks" auto-grow  :rules="[requiredValidator]" />
            </VCol>
          </VRow>
        </VCardText>
        <VCardText class="d-flex justify-end gap-4 flex-wrap">
          <VBtn color="secondary" variant="tonal" @click="isDialogVisible = !isDialogVisible">
            Close
          </VBtn>
          <VBtn color="error" type="submit">
            Sure!
          </VBtn>
        </VCardText>
      </VCard>
    </VForm>

  </VDialog>
    <VDialog
      v-model="noticeDialog"
      width="500"
    >

      <!-- Dialog close btn -->
      <DialogCloseBtn @click="noticeDialog = !noticeDialog" />

      <!-- Dialog Content -->
      <VCard title="Cancel Booking Policy">
        <VCardText>
         Please Make Sure Cancel Your Booking 1 Day Before
        </VCardText>

        <VCardText class="d-flex justify-end">
          <VBtn @click="noticeDialog = false">
           close
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
