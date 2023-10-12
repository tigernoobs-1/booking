<script setup lang="ts">
import { useApi } from '@/plugins/axios';
import type { BookingProperties } from '@/views/apps/admin/types';
import { requiredValidator, timeValidator } from '@validators';
import moment from 'moment';
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';
import { VForm } from 'vuetify/components';

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'facilityData', value: BookingProperties): void
  (e: 'updateData', value: BookingProperties): void
}

interface Props {
  isDrawerOpen: boolean
  title?: string
  data: {
    facility: any;
    remarks: any;
    start_time: any;
    end_time: any;
    group: string;
    id: number; name: string; description: Text; status: boolean
  }
}

const props = defineProps<Props>()

const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref<VForm>()
const facilityName = ref()
const facilityDescription = ref()
const facilityStatus = ref()
const date = ref()
const newDate = ref()
const startTime = ref()
const endTime = ref()
const facilityList = ref()
const itemOption = ref()
const bookingList = ref()
const maxDate = ref()
const newBookingList = ref()
let timeSlot: string[] = []
const listTime = ref()
const startListTime = ref()
const endListTime = ref()
const enabledDate = ref()
const timeConfig = ref()
const dayConfig = ref()
const showDisclaimer = ref(false)
const disclaimerText = ref()
const initialTime = startTime.value

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
      const userDataString = localStorage.getItem('userData')
      const userdata = userDataString ? JSON.parse(userDataString) : null
      const dateObj = moment(date.value)
      const start = moment(startTime.value, 'HH:mm:ss')
      //const end = moment(endTime.value, 'HH:mm:ss')
      dateObj.set({
        hour: start.hour(),
        minute: start.minute(),
        second: start.second()
      });
      const startDateTime = dateObj.format('YYYY-MM-DD HH:mm:ss');
      /* dateObj.set({
        hour: end.hour(),
        minute: end.minute(),
        second: end.second()
      }); */
      //const endDateTime = dateObj.format('YYYY-MM-DD HH:mm:ss');
      const endDateTime = moment(startDateTime).add(1, 'hour').format('YYYY-MM-DD HH:mm:ss')
      const formData = {
        user_id: userdata.id,
        item_id: facilityName.value,
        status: facilityStatus.value,
        remarks: facilityDescription.value,
        start_time: startDateTime,
        end_time: endDateTime,
      }

      if (valid && props.title === 'Create')
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

const findBooking = async () => {
  const api = useApi()
  const { data } = await api.get('find-booking')
  bookingList.value = data.data
}

const setFacility = async () => {
  const api = useApi()
  const { data } = await api.get('item')
  facilityList.value = data.data
  const list = data.data.filter((obj: { status: number }) => obj.status === 1)
  const listOptions = [] as { value: string, title: string }[]
  for (let i = 0; i < list.length; i++)
    listOptions.push({ value: list[i].id, title: list[i].name })
  itemOption.value = listOptions
}

const getTimeConfig = async () => {
  const api = useApi()
  const { data } = await api.get('config-time')
  console.log('getTimeConfig > data: ', data)
  timeConfig.value = data.data
  //console.log(timeConfig.value)
}

const getDayConfig = async () => {
  const api = useApi()
  const { data } = await api.get('config-day')
  dayConfig.value = data.data

  const userData = JSON.parse(localStorage.getItem('userData'))
  const userGroup = userData.group
  dayConfig.value.forEach((e) => {
    if (e.config_value.group.includes(userGroup)) {
      //console.log(e.config_value.available_day)
      if (e.config_value.facility_id.includes(facilityName.value)) {
        enabledDate.value = getDatesByDayOfWeek(e.config_value.available_day, 30)
      }
      else {
        enabledDate.value = getDatesByDayOfWeek([1, 2, 3, 4, 5, 6], 30)
      }

    }
  })
}

const handleDrawerModelValueUpdate = (val: boolean) => {
  emit('update:isDrawerOpen', val)
}
const getFacility = () => {
  if (facilityName.value) {
    startTime.value = initialTime
    console.log(startTime.value)
    getDayConfig()
    date.value = ''
    startListTime.value = []
    showDisclaimer.value = false
    const selectedFacility = facilityList.value.filter((item: { id: any }) => item.id === facilityName.value)[0]
    if (selectedFacility.disclaimer) {
      showDisclaimer.value = true
      disclaimerText.value = selectedFacility.disclaimer_text
    }
  }
}


const dateTime = () => {
  if (facilityName.value) {
    const selectedFacility = facilityList.value.filter((item: { id: any }) => item.id === facilityName.value)[0]
    const timeNow = moment(new Date()).format('HH:mm:ss')
    const dateNow = moment(new Date()).format('YYYY-MM-DD')

    const dayOfWeek = moment(date.value).toDate().getDay();
    //console.log(dayOfWeek)
    const newTime = ref()
    const maxtime = ref()
    console.log(enabledDate.value)
    console.log(date.value)
    console.log('timeConfig', timeConfig.value)
    timeConfig.value.forEach((e) => {
      if (e.config_value.facility_id.includes(facilityName.value)) {
        if (dayOfWeek == e.config_value.day) {
          newTime.value = e.config_value.start
          maxtime.value = e.config_value.end
        }
      }

    })
    /* if (dayOfWeek >= 1 && dayOfWeek <= 5) {
      newTime.value = '17:00:00'
      maxtime.value = '23:00:00'
    } else {
      newTime.value = '08:00:00'
      maxtime.value = '17:00:00'
    } */

    if (dateNow === date.value) {
      const component = timeNow.split(':')
      let hour = parseInt(component[0])
      let minute = component[1]
      let second = component[2]
      if (parseInt(minute) > 30) {
        hour = +hour + 1
        minute = '00'
      }
      else {
        if (selectedFacility.maximum_time === 30) {
          minute = '30'
        }
        else {
          hour = +hour + 1
          minute = '00'
        }
      }
      second = '00'
      newTime.value = hour + ":" + minute + ":" + second
    }
    const startTime = newTime.value // set dynamic start time
    const endTime = maxtime.value // set dynamic end time
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
  if (props.data) {
    // console.log(props.data)
    facilityName.value = props.data.facility.id
    facilityDescription.value = props.data.remarks
    const startDateTime = props.data.start_time.split(' ')
    date.value = startDateTime[0]
    newDate.value = startDateTime[0]
    startTime.value = startDateTime[1]
    newBookingList.value = bookingList.value.filter((item: { id: number }) => item.id !== props.data.id);
  }
  else {
    facilityStatus.value = 'Active'
    date.value = new Date()
    newBookingList.value = bookingList.value
    getFacility()
  }
}



const today = new Date()
const yesterday = new Date()
yesterday.setDate(today.getDate() - 1)
maxDate.value = moment(yesterday).format('YYYY-MM-DD')
enabledDate.value = getDatesByDayOfWeek([], 30)



function getDatesByDayOfWeek(dayOfWeek: number[], rangeInDays: number): string[] {
  const currentDate = moment();
  const startDate = currentDate.clone().startOf('day');
  const endDate = currentDate.clone().add(rangeInDays, 'days').endOf('day');
  const dates: string[] = [];

  let currentDatePointer = startDate.clone();

  while (currentDatePointer.isSameOrBefore(endDate)) {
    if (dayOfWeek.includes(currentDatePointer.day())) {
      dates.push(currentDatePointer.format('YYYY-MM-DD'));
    }
    currentDatePointer.add(1, 'day');
  }

  //console.log('dates', dates)
  return dates;
}


onMounted(() => {
  watch(props, async () => {
    await setFacility()
    await findBooking()
    await getTimeConfig()
    await getDayConfig()
    setData()
  })
})
</script>

<template>
  <VNavigationDrawer temporary :width="800" location="end" class="scrollable-content" :model-value="props.isDrawerOpen"
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
              <!-- 👉 Name -->
              <VCol cols="12">
                <VSelect v-model="facilityName" :rules="[requiredValidator]" :items="itemOption" label="Facility"
                  @update:model-value="getFacility" />
              </VCol>

              <VCol cols="8" md="12">
                <VAlert v-model="showDisclaimer" variant="tonal" color="warning">
                  <p class="text-caption mb-0">
                    {{ disclaimerText }}
                  </p>
                </VAlert>
              </VCol>


              <VCol cols="12" md="6">
                <AppDateTimePicker :key="enabledDate" v-model="date" label="Inline"
                  :config="{ inline: true, enable: enabledDate }" class="calendar-date-picker"
                  @update:model-value="dateTime" :rules="[requiredValidator]" />
                  
              </VCol>
              <VCol cols="12" md="6">
                <VSelect v-model="startTime" :key="startTime" :rules="[requiredValidator]" :items="startListTime" label="Start Time"
                  @update:model-value="setEndTime" />
              </VCol>
             <!--   <VCol cols="12">
                  <VSelect v-model="endTime" :rules="[requiredValidator, timeValidator(startTime, endTime)]"
                    :items="endListTime" label="End Time" />
                </VCol> -->

              <!-- 👉 Description -->
              <VCol cols="12">
                <VTextarea v-model="facilityDescription" label="Notes" auto-grow />
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
<style lang="scss">
@use "@core-scss/template/libs/full-calendar";

.calendar-date-picker {
  display: none;

  +.flatpickr-input {
    +.flatpickr-calendar.inline {
      border: none;
      box-shadow: none;

      .flatpickr-months {
        border-block-end: none;
      }
    }
  }
}
</style>
