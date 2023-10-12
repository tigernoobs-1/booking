<script setup lang="ts">
import UserForm from '@/views/apps/admin/form/UserForm.vue';
import type { UserProperties } from '@/views/apps/admin/types';
import { useFacilityListStore } from '@/views/apps/admin/useFacilitiesStore';
import { utils, writeFileXLSX } from 'xlsx';

// 👉 Store
const facilityListStore = useFacilityListStore()
const isDialogVisible = ref(false)
const facilityId = ref()
const searchQuery = ref('')
const rowPerPage = ref(20)
const currentPage = ref(1)
const totalPage = ref(1)
const userData = ref()
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

  userData.value = {
    facility: filteredFacilities,
  }

  //console.log(userData.value.facility)
}

// 👉 Fetching facilities
const fetchFacilities = () => {
  facilityListStore.fetchUser().then(response => {
    responseData.value = response.data.data
    //console.log(responseData.value)
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

const resolveUserStatusVariant = (role: string) => {
  if (role === 'user')
    return 'primary'

  return 'success'
}

const colorStatus = (e) => {
  if (e === 1)
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
  if (userData.value) {
    const firstIndex = userData.value.facility.length ? ((currentPage.value - 1) * rowPerPage.value) + 1 : 0
    const lastIndex = userData.value.facility.length + ((currentPage.value - 1) * rowPerPage.value)

    return `Showing ${firstIndex} to ${lastIndex} of ${responseData.value.length} entries`
  }
})

// 👉 Delete Facility
const catchId = (e: any) => {
  facilityId.value = e
  isDialogVisible.value = true
}

const deleteFacility = async (e: boolean) => {
  if (e) {
    await facilityListStore.deleteUser(facilityId.value)
    isDialogVisible.value = false
    await fetchFacilities()
  }
  else {
    facilityId.value = ''
    isDialogVisible.value = false
  }
}

// 👉 Update Facility
const updateFacility = async (updateData: UserProperties) => {
  await facilityListStore.updateUser(updateData, selectedFacility.value.id)

  // refetch Facility

  await fetchFacilities()
}

const formEvent = (e: any) => {
  if (e) {
    isAddNewUserDrawerVisible.value = true

    selectedFacility.value = userData.value.facility.filter((facility: { id: any; }) => facility.id === e)[0]

    formTitle.value = 'Update'
  }
}

function exportFile() {
  const facilitiesWithoutId = responseData.value.map(({ id, ...facility }) => facility)
  const ws = utils.json_to_sheet(facilitiesWithoutId)
  const wb = utils.book_new()

  utils.book_append_sheet(wb, ws, 'Data')
  writeFileXLSX(wb, 'userList.xlsx')
}
</script>

<template>
  <section v-if="userData">
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardText class="d-flex flex-wrap py-4 gap-4">
            <div class="me-3" style="width: 80px;">
              <VSelect v-model="rowPerPage" density="compact" variant="outlined" :items="[20, 40, 60]"
                @update:model-value="filterData" />
            </div>

            <VSpacer />

            <div class="app-user-search-filter d-flex flex-row-reverse align-center flex-wrap gap-6">
              <!-- 👉 Search  -->
              <div style="width: 15rem;">
                <VTextField v-model="searchQuery" placeholder="Search" density="compact" @keyup="filterData" />
              </div>

              <!-- 👉 Export button -->
              <VBtn variant="tonal" color="primary" prepend-icon="tabler-screen-share" @click="exportFile">
                Export
              </VBtn>

              <!-- 👉 Add user button -->
              <!-- <VBtn
                prepend-icon="tabler-plus"
                @click="formEvent('')"
              >
                Add Facility
              </VBtn> -->
            </div>
          </VCardText>

          <VDivider />

          <VTable class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col">
                  NAME / EMAIL
                </th>
                <th scope="col">
                  COMPANY
                </th>
                <th scope="col">
                  ROLE
                </th>
                <th scope="col">
                  GROUP
                </th>
                <th scope="col">
                  IS REGISTERED
                </th>
                <th scope="col">
                  IS ACTIVE
                </th>
                <th scope="col">
                  ACTIONS
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr v-for="facility in userData.facility" :key="facility.id" style="height: 3.75rem;">
                <!-- 👉 Name -->
                <td>
                  <div class="d-flex align-center">
                    {{ facility.name }} <br />
                    {{ facility.email }}
                  </div>
                </td>
                <!-- 👉 Email -->
                <td>
                  {{ facility.company }}
                </td>

                <!-- 👉 Status -->
                <td>
                  <VChip label :color="resolveUserStatusVariant(facility.role)" size="small" class="text-capitalize">
                    {{ facility.role }}
                  </VChip>
                </td>
                <td>
                  {{ facility.group }}
                </td>
                <td>
                  <VChip label :color="colorStatus(facility.is_registered)" size="small" class="text-capitalize">
                    {{ facility.is_registered === 1 ? 'true' : 'false' }}
                  </VChip>
                </td>

                <td>
                  <VChip label :color="colorStatus(facility.is_active)" size="small" class="text-capitalize">
                    {{ facility.is_active === 1 ? 'true' : 'false' }}
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
    <UserForm v-model:isDrawerOpen="isAddNewUserDrawerVisible" :title="formTitle" :data="selectedFacility"
      @update-data="updateFacility" />
  </section>
  <VDialog v-model="isDialogVisible" persistent class="v-dialog-sm">
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" />

    <!-- Dialog Content -->
    <VCard title="Comfirm Delete User?">
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
