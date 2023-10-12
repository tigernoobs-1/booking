<script setup lang="ts">
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { VForm } from 'vuetify/components'
import { requiredValidator } from '@validators'
import type { FacilityProperties } from '@/views/apps/admin/types'

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'facilityData', value: FacilityProperties): void
  (e: 'updateData', value: FacilityProperties): void
}

interface Props {
  isDrawerOpen: boolean
  title?: string
  data: { name: string; description: Text; status: boolean }
}


const maxTime = [{ value: 30, title: '30 Minutes' }, { value: 60, title: '1 Hour' }, { value: 120, title: '2 Hour' }, { value: 180, title: '3 Hour' }]

const props = defineProps<Props>()

const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref<VForm>()
const facilityName = ref()
const facilityDescription = ref()
const facilityStatus = ref('Disable')
const timeLimit = ref()
const bookingLimit = ref(1)

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
      const statusValue = ref()
      if (facilityStatus.value === 'Active')
        statusValue.value = true
      else
        statusValue.value = false

      const formData = {
        name: facilityName.value,
        description: facilityDescription.value,
        status: statusValue.value,
        maximum_time: timeLimit.value,
        limit_user: bookingLimit.value
      }

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

const setData = () => {
  if (props.data) {
    console.log(props.data)
    facilityName.value = props.data.name
    facilityDescription.value = props.data.description
    timeLimit.value = props.data.maximum_time
    if (props.data.status.toString() === '1')
      facilityStatus.value = 'Active'
    else
      facilityStatus.value = 'Disable'
  }
}

onMounted(() => {
  watch(props, () => {
    setData()
  })
})

const test = (e) => {
  console.log(e)
}
</script>

<template>
  <VNavigationDrawer temporary :width="400" location="end" class="scrollable-content" :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate">
    <!-- 👉 Title -->
    <div class="d-flex align-center  pa-6 pb-1">
      <h6 class="text-h6">
        {{ props.title }} Facility
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
                <VTextField v-model="facilityName" :rules="[requiredValidator]" label="Facility Name" />
              </VCol>

              <!-- 👉 Description -->
              <VCol cols="12">
                <VTextarea v-model="facilityDescription" :rules="[requiredValidator]" label="Description" auto-grow />
              </VCol>

              <!-- 👉 Max Time -->
              <VCol cols="12">
                <VSelect v-model="timeLimit" :items="maxTime" item-title="title" :rules="[requiredValidator]" item-value="value" label="Maximum Time"
                  @update:model-value="test" />
              </VCol>
              

              <!-- 👉 Max User -->
              <!-- <VCol cols="12">
                <VTextField v-model="bookingLimit" type="number" :rules="[requiredValidator]" label="User Per Booking"
                  auto-grow />
              </VCol>
 -->
              <!-- 👉 Status -->
              <VCol cols="12">
                <VSwitch v-model="facilityStatus" :label="facilityStatus" true-value="Active" false-value="Disable" />
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
