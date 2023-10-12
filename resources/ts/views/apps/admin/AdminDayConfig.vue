<script setup lang="ts">
import DayConfig from '@/views/apps/admin/form/DayConfigForm.vue';
import type { ConfigTime } from '@/views/apps/admin/types';
import { useFacilityListStore } from '@/views/apps/admin/useFacilitiesStore';


// 👉 Store
const facilityListStore = useFacilityListStore()
const isDialogVisible = ref(false)
const facilityId = ref()
const facilitiesData = ref()
const formTitle = ref()
const responseData = ref()
const selectedFacility = ref()
const isAddNewUserDrawerVisible = ref(false)


const dayList = [
  { value: 0, title: 'Sunday' },
  { value: 1, title: 'Monday' },
  { value: 2, title: 'Tuesday' },
  { value: 3, title: 'Wednesday' },
  { value: 4, title: 'Thursday' },
  { value: 5, title: 'Friday' },
  { value: 6, title: 'Saturday' }

]

// 👉 Fetching facilities
const fetchFacilities = async () => {
  responseData.value = ''
  try {
    const response = await facilityListStore.fetchDayConfig();
    const mergedList = response.data.data;

    facilitiesData.value = mergedList.map(item => {
      const availableDays = item.config_value.available_day.map(dayValue => {
        const foundDay = dayList.find(dayItem => dayItem.value === dayValue);
        return foundDay ? foundDay.title : null;
      });

      return {
        ...item,
        day: availableDays.filter(day => day !== null)
      };
    });

  } catch (error) {
    console.error(error);
  }
};

watchEffect(fetchFacilities)

const resolveUserStatusVariant = (stat: boolean) => {
  if (stat)
    return 'success'
  return 'secondary'
}

// 👉 Add new Facility
const addNewFacility = (facilityData: ConfigTime) => {
  facilityListStore.addConfig(facilityData)

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

</script>

<template>
  <section v-if="facilitiesData">
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardText class="d-flex flex-wrap py-4 gap-4">

            <VSpacer />

            <div class="app-user-search-filter d-flex flex-row-reverse align-center flex-wrap gap-4">
              <!-- 👉 Search  -->
              <!-- <div style="width: 10rem;">
                <VTextField v-model="searchQuery" placeholder="Search" density="compact" @keyup="filterData" />
              </div> -->

              <!-- 👉 Export button -->
              <!-- <VBtn variant="tonal" color="primary" prepend-icon="tabler-screen-share" @click="exportFile">
                Export
              </VBtn> -->

              <!-- 👉 Add user button -->

              <VBtn prepend-icon="tabler-plus" @click="formEvent('')">
                Add Group
              </VBtn>

            </div>
          </VCardText>

          <VDivider />
          <VTable class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col">
                  Group
                </th>
                <th scope="col">
                  List Facility
                </th>
                <!--  <th scope="col">
                  Maximum Booking
                </th> -->
                <th scope="col">
                  Available Day
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
                    {{ facility.config_value.group }}

                  </div>
                </td>

                <td>
                  <div v-for="item in facility.facility">
                    {{ item }}
                  </div>
                </td>
                <!-- 👉 Description -->
                <td>
                  <div v-for="item in facility.day">
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
          </VTable>

          <VDivider />

          <VCardText class="d-flex align-center flex-wrap justify-space-between gap-4 py-3 px-5">
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- 👉 Add New User -->
    <DayConfig v-model:isDrawerOpen="isAddNewUserDrawerVisible" :title="formTitle" :data="selectedFacility"
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
