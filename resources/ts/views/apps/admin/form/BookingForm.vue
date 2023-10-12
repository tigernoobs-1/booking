<script setup lang="ts">
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { VForm } from 'vuetify/components'
import { requiredValidator, timeValidator } from '@validators'
import type { BookingProperties } from '@/views/apps/admin/types'
import { useApi } from '@/plugins/axios'
import moment from 'moment'

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'facilityData', value: BookingProperties): void
  (e: 'updateData', value: BookingProperties): void
}

interface Props {
  isDrawerOpen: boolean
  title?: string
  data: { id: number; user: { id: number, name: string, email: string }; facility: { id: number, name: string }, status: string, start_time: string, end_time: string, remarks: Text }
}

const props = defineProps<Props>()

const emit = defineEmits<Emit>()

const errors = ref<Record<string, string | undefined>>({
  email: undefined,
})

const bookingStatus = [
  'Active',
  'Completed',
  'Cancel',
]

const isFormValid = ref(false)
const refForm = ref<VForm>()
const facilityName = ref()
const date = ref()
const startTime = ref()
const endTime = ref()
const facilityDescription = ref()
const facilityStatus = ref()
const listTime = ref()
const selectedUser = ref()
const userOption = ref()
const itemOption = ref()
const facilityList = ref()
const bookingList = ref()
const newBookingList = ref()
const startListTime = ref()
const endListTime = ref()
let timeSlot: string[] = []
// const status = ref()

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)

  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
  })
}


const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      const dateObj = moment(date.value)
      const start = moment(startTime.value, 'HH:mm:ss')
      const end = moment(endTime.value, 'HH:mm:ss')
      dateObj.set({
        hour: start.hour(),
        minute: start.minute(),
        second: start.second()
      });
      const startDateTime = dateObj.format('YYYY-MM-DD HH:mm:ss');
      dateObj.set({
        hour: end.hour(),
        minute: end.minute(),
        second: end.second()
      });
      const endDateTime = dateObj.format('YYYY-MM-DD HH:mm:ss');
      //console.log(selectedUser.value.value)
      //console.log(facilityName.value)
      const formData = {
        user_id: selectedUser.value.value,
        item_id: facilityName.value,
        status: facilityStatus.value,
        remarks: facilityDescription.value,
        start_time: startDateTime,
        end_time: endDateTime,
      }

      //console.log(formData)

      if (valid && props.title === 'Add')
        emit('facilityData', formData)

      if (valid && props.title === 'Update')
        emit('updateData', formData)

      emit('update:isDrawerOpen', false)
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
      })
    }
  })
}

const handleDrawerModelValueUpdate = (val: boolean) => {
  emit('update:isDrawerOpen', val)
}

const findBooking = async () => {
  const api = useApi()
  const { data } = await api.get('find-booking')
  bookingList.value = data.data
  //console.log(bookingList.value)
}

const setFacility = async () => {
  const api = useApi()
  const { data } = await api.get('item')
  facilityList.value = data.data
  const list = data.data.filter((obj: { status: number }) => obj.status === 1)
  const listOptions: [{ value: string, title: string }] = []
  for (let i = 0; i < list.length; i++)
    listOptions.push({ value: list[i].id, title: list[i].name })
  itemOption.value = listOptions
}

const setUser = async () => {
  const api = useApi()
  const list = await api.get('user')
  const listOptions: [{ value: string, title: string }] = []
  for (let i = 0; i < list.data.data.length; i++)
    listOptions.push({ value: list.data.data[i].id, title: list.data.data[i].name })
  //usersArray.push(userList.value[i].name)
  userOption.value = listOptions
}

const dateTime = () => {
  //console.log(facilityName.value)
  if (facilityName.value) {
    //console.log('masuk')
    const selectedFacility = facilityList.value.filter((item: { id: any }) => item.id === facilityName.value)[0]
    const startTime = '08:00:00' // set dynamic start time
    const endTime = '23:00:00' // set dynamic end time
    const duration = selectedFacility.maximum_time // set dynamic duration in minutes
    let time = new Date(`2023-04-06 ${startTime}`)// create a new Date object with dynamic start time
    timeSlot = [] 
    while (time <= new Date(`2023-04-06 ${endTime}`)) { // loop until dynamic end time is reached
      timeSlot.push(time.toLocaleTimeString('en-US', { hour12: false }))// add current time to time slot array
      time = new Date(time.getTime() + duration * 60000) // increment time by dynamic duration
    }
  }
  const filteredData = newBookingList.value.filter((item: { end_time: string; facility: { id: any } }) => item.end_time.startsWith(date.value) && item.facility.id === facilityName.value)
  listTime.value = [...timeSlot]
   if (filteredData.length > 0) {
    const slotsToRemove: any[] = []
    for (let i = 0; i < filteredData.length; i++) {
      const start = new Date(filteredData[i].start_time).toLocaleTimeString('en-US', { hour12: false })
      const end = new Date(filteredData[i].end_time).toLocaleTimeString('en-US', { hour12: false })
      listTime.value.forEach((time: any) => {
        if (time >= start && time < end) {
          slotsToRemove.push(time)
        }
      })
    }
    startListTime.value = listTime.value.filter((time: any) => !slotsToRemove.includes(time))
  } else {
    startListTime.value = listTime.value
  }
}

const setEndTime = (startTime: any) => {
  const filteredData = newBookingList.value.filter((item: { end_time: string; facility: { id: any } }) => item.end_time.startsWith(date.value) && item.facility.id === facilityName.value)
  listTime.value = [...timeSlot]
  endListTime.value = listTime.value.filter((time: any) => time > startTime);
  if (filteredData.length > 0) {
    const slotsToRemove: any[] = []
    for (let i = 0; i < filteredData.length; i++) {
      const start = new Date(filteredData[i].start_time).toLocaleTimeString('en-US', { hour12: false })
      const end = new Date(filteredData[i].end_time).toLocaleTimeString('en-US', { hour12: false })
      if (startTime < start) {
        slotsToRemove.push(...endListTime.value.filter((time: any) => time > start))
        break
      }
    }
    endListTime.value = endListTime.value.filter((time: any) => !slotsToRemove.includes(time))
  }
}


const setData = () => {
  if (props.title === 'Update') {
    //console.log(props.data)
    facilityName.value = props.data.facility.id
    const startDateTime = props.data.start_time.split(' ')
    const endDateTime = props.data.end_time.split(' ')
    //console.log(startDateTime)
    date.value = startDateTime[0]
    startTime.value = startDateTime[1]
    endTime.value = endDateTime[1]
    newBookingList.value = bookingList.value.filter((item: { id: number }) => item.id !== props.data.id);
    selectedUser.value = [{ value: props.data.user.id, title: props.data.user.name }]
    facilityDescription.value = props.data.remarks
    facilityStatus.value = props.data.status
    dateTime()
  }
  else {
    facilityStatus.value = 'Active'
    date.value = new Date()
    newBookingList.value = bookingList.value
    dateTime()
  }
}

onMounted(() => {
  watch(props, async () => {
    await setFacility()
    await findBooking()
    await setUser()
    setData()
  })
})
</script>

<template>
  <VNavigationDrawer temporary :width="400" location="end" class="scrollable-content" :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate">
    <!-- 👉 Title -->
    <div class="d-flex align-center  pa-6 pb-1">
      <h6 class="text-h6">
        {{ props.title }} Booking
      </h6>

      <VSpacer />

      <!-- 👉 Close btn -->
      <VBtn variant="tonal" color="default" icon size="32" class="rounded" @click="closeNavigationDrawer">
        <VIcon size="18" icon="tabler-x" />
      </VBTn>
    </div>

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <!-- 👉 Form -->
          <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
            <VRow>
              <VCol cols="12">
                <VSelect v-model="facilityName" :items="itemOption" :rules="[requiredValidator]" item-title="title"
                  item-value="value" label="Facility" @update:model-value="dateTime" />
              </VCol>
              <!-- 👉 Name -->
              <VCol cols="12">
                <AppDateTimePicker v-model="date" label="Date" :rules="[requiredValidator]"
                  @update:model-value="dateTime" />
              </VCol>
              <!-- 👉 Name -->
              <VCol cols="12">
                <VSelect v-model="startTime" :rules="[requiredValidator]" :items="startListTime" label="Start Time"  @update:model-value="setEndTime" />
              </VCol>
              <VCol cols="12">
                <VSelect v-model="endTime" :items="endListTime"
                  :rules="[requiredValidator, timeValidator(startTime, endTime)]" label="End Time" />
              </VCol>
              <VCol cols="12">
                <VCombobox v-model="selectedUser" :rules="[requiredValidator]" :items="userOption" item-title="title"
                  item-value="value" label="User Name" />
              </VCol>
              <!-- 👉 Name -->
              <VCol v-if="props.title === 'Update'" cols="12">
                <VSelect v-model="facilityStatus" :items="bookingStatus" label="Status" />
              </VCol>

              <!-- 👉 Description -->
              <VCol cols="12">
                <VTextarea v-model="facilityDescription" label="Remarks" auto-grow />
              </VCol>

              <!-- 👉 Submit and Cancel -->
              <VCol cols="12">
                <VBtn type="submit" class="me-3">
                  {{ props.title }}
                </VBtn>
                <VBtn type="reset" variant="tonal" color="secondary" @click="closeNavigationDrawer">
                  Cancel
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
