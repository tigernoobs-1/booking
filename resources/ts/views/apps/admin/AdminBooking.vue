<script setup lang="ts">
import { read, utils, writeFileXLSX } from 'xlsx'
import BookingForm from '@/views/apps/admin/form/BookingForm.vue'
import { requiredValidator } from '@validators'
import { useFacilityListStore } from '@/views/apps/admin/useFacilitiesStore'
import type { BookingProperties } from '@/views/apps/admin/types'
import moment from 'moment'
import { VForm } from 'vuetify/components';
import { useApi } from '@/plugins/axios';

// 👉 Store
const facilityListStore = useFacilityListStore()
const isDialogVisible = ref(false)
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
const userGroup = ref()
const selectedGroup = ref()


const filterData = () => {
  const facilities = responseData.value
  console.log(selectedGroup.value)
  const queryLower = searchQuery.value.toLowerCase()
  //let filteredFacilities = facilities.filter((facility: { user: { user_group: string, email:string }, facility:{name:string} }) => ((facility.user.user_group.toLowerCase().includes(queryLower)) || (facility.user.email.toLowerCase().includes(queryLower)) || (facility.facility.name.toLowerCase().includes(queryLower))  )).reverse()
  let filteredFacilities = facilities.filter((facility: { user: { user_group: string; email: string }; facility: { name: string } }) => {
    const userGroup = (facility.user && facility.user.user_group) ? facility.user.user_group.toLowerCase() : '';
    const email = (facility.user && facility.user.email) ? facility.user.email.toLowerCase() : '';
    const facilityName = (facility.facility && facility.facility.name) ? facility.facility.name.toLowerCase() : '';
    return (userGroup.includes(queryLower) || email.includes(queryLower) || facilityName.includes(queryLower)) && facility.user.user_group === (selectedGroup.value || facility.user.user_group);
  }).reverse();
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

const fetchBookings = () => {
  facilityListStore.fetchBookingAdmin().then(response => {
    responseData.value = response.data.data
    //console.log(responseData.value)
    filterData()
  }).catch(error => {
    console.error(error)
  })
}

const getGroupConfig = async () => {
  const api = useApi()
  const { data } = await api.get('user-group')
  userGroup.value = data
  console.log(userGroup.value)
}


watchEffect(fetchBookings)

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

const addNewBooking = async (BookingData: BookingProperties) => {
  await facilityListStore.addBooking(BookingData)
  await fetchBookings()
}


const catchId = (e: any) => {
  selectedFacility.value = facilitiesData.value.facility.filter((facility: { id: any; }) => facility.id === e)[0]
  isDialogVisible.value = true
}

const deleteBooking = async (updateData: BookingProperties) => {
  await facilityListStore.deleteBooking(updateData, selectedFacility.value.id)
  await fetchBookings();
  isDialogVisible.value = false
}


const updateBooking = async (updateData: BookingProperties) => {
  console.log('updatedata', updateData)
  if (updateData.status === 'Cancel') {
    await deleteBooking(updateData)
  } else {
    await facilityListStore.updateBooking(updateData, selectedFacility.value.id)
  }
  await fetchBookings()
}

const formEvent = (e: any) => {
  if (e) {
    isAddNewUserDrawerVisible.value = true

    selectedFacility.value = facilitiesData.value.facility.filter((facility: { id: any }) => facility.id === e)[0]
    formTitle.value = 'Update'
  }
  else {
    formTitle.value = 'Add'
    isAddNewUserDrawerVisible.value = true
    selectedFacility.value = facilitiesData.value.facility
  }
}

function exportFile() {
  const facilitiesWithoutId = responseData.value.map(({ id, ...facility }) => facility)
  const updatedDataArray = facilitiesWithoutId.map((obj: { facility: { name: any }, user: { name: any } }) => {
    return {
      ...obj,
      facility: obj.facility.name,
      user: obj.user.name
    };
  });
  const ws = utils.json_to_sheet(updatedDataArray)
  const wb = utils.book_new()

  utils.book_append_sheet(wb, ws, 'Data')
  writeFileXLSX(wb, 'bookingList.xlsx')
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
  getGroupConfig()
})
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
            <VSelect v-model="selectedGroup" label="Select Group" :items="userGroup" clearable clear-icon="tabler-x"
              @update:model-value="filterData" />


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
                Add Booking
              </VBtn>
            </div>
          </VCardText>

          <VDivider />

          <VTable class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col">
                  FACILITY
                </th>
                <th scope="col">
                  START / END
                </th>
                <!--    <th scope="col">
                  END
                </th> -->
                <th scope="col">
                  USER DETAILS
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
              <tr v-for="facility in facilitiesData.facility" :key="facility.id" style="height: 3.75rem;">
                <!-- 👉 Name -->
                <td>
                  <div class="d-flex align-center">
                    {{ facility.facility.name }}
                  </div>
                </td>

                <!-- 👉 Description -->
                <td>
                  {{ moment(facility.start_time).format('DD/MM/YYYY h:mm A') }} -
                  {{ moment(facility.end_time).format('h:mm A') }}
                </td>

                <!-- 👉 Description -->
                <td>
                  {{ facility.user.name }} <br>
                  {{ facility.user.email }} <br>
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
                <td class="text-center" style="width: 5rem;" v-if="facility.status === 'Active'">
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
    <BookingForm v-model:isDrawerOpen="isAddNewUserDrawerVisible" :title="formTitle" :data="selectedFacility"
      @facility-data="addNewBooking" @update-data="updateBooking" />
  </section>
  <VDialog v-model="isDialogVisible" persistent class="v-dialog-sm">
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" />

    <!-- Dialog Content -->
    <VForm ref="refForm" v-model="isFormValid" @submit.prevent="submitForm">
      <VCard title="Are you sure to Reject this booking?">
        <VCardText>
          <VRow>
            <VCol>
              <VTextarea v-model="remark" label="Remarks" auto-grow :rules="[requiredValidator]" />
            </VCol>
          </VRow>
        </VCardText>
        <VCardText class="d-flex justify-end gap-4 flex-wrap">
          <VBtn color="secondary" variant="tonal" @click="isDialogVisible = !isDialogVisible">
            Close
          </VBtn>
          <VBtn color="error" type="submit">
            Reject
          </VBtn>
        </VCardText>
      </VCard>
    </VForm>
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
