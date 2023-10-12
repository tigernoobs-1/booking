<script setup lang="ts">
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { VForm } from 'vuetify/components'
import { requiredValidator } from '@validators'
import type { ConfigTime } from '@/views/apps/admin/types'
import { useApi } from '@/plugins/axios'

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'facilityData', value: ConfigTime): void
  (e: 'updateData', value: ConfigTime): void
}

interface Props {
  isDrawerOpen: boolean
  title?: string
  data: { name: string; description: Text; status: boolean }
}

const hoursArray: string[] = [];
for (let hour = 8; hour < 24; hour++) {
  const formattedHour = hour.toString().padStart(2, '0') + ":00:00";
  hoursArray.push(formattedHour);
}

const props = defineProps<Props>()

const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref<VForm>()
const facilityName = ref()
const facilityDescription = ref()
const facilityStatus = ref('Disable')
const startTime = ref()
const endTime = ref()
const bookingLimit = ref(1)
const facilityOption = ref()
const facilityList = ref()
const selectedOptions = ref()

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

      const configData = {
        day_name: facilityName.value,
        end: endTime.value,
        start: startTime.value,
        facility_id: selectedOptions.value,
        day: props.data.config_value.day
      }

      const jsonData = JSON.stringify(configData)
      const formData = {
        is_active: statusValue.value,
        config_value: jsonData
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

const setUser = async () => {
  const api = useApi()
  const { data } = await api.get('item')
  facilityList.value = data.data
  const list = data.data.filter((obj: { status: number }) => obj.status === 1)
  console.log(facilityList)
  const listOptions: [{ value: string, title: string }] = []
  for (let i = 0; i < list.length; i++)
    listOptions.push({ value: list[i].id, title: list[i].name })
  //usersArray.push(userList.value[i].name)
  facilityOption.value = listOptions
  console.log(facilityOption.value)
}


const setData = () => {
  if (props.data) {
    facilityName.value = props.data.config_value.day_name
    facilityDescription.value = props.data.description
    selectedOptions.value = props.data.config_value.facility_id
    startTime.value = props.data.config_value.start
    endTime.value = props.data.config_value.end
    console.log(props.data.config_value.facility_id)
    if (props.data.status.toString() === '1')
      facilityStatus.value = 'Active'
    else
      facilityStatus.value = 'Disable'
  }
}


const getFacility = () => {
  console.log(selectedOptions.value)
}

onMounted(() => {
  watch(props, () => {
    setUser()
    setData()
  })
})

const test = (e: any) => {
  console.log(e)
}
</script>

<template>
  <VNavigationDrawer temporary :width="400" location="end" class="scrollable-content" :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate">
    <!-- 👉 Title -->
    <div class="d-flex align-center  pa-6 pb-1">
      <h6 class="text-h6">
        {{ props.title }} For ({{ facilityName }})
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
                <VSelect v-model="selectedOptions" :items="facilityOption" :menu-props="{ maxHeight: '400' }"
                  label="Facility" item-value="value" multiple persistent-hint @update:model-value="getFacility" />

              </Vcol>
              <!-- 👉 Max Time -->
              <VCol cols="12">
                <VSelect v-model="startTime" :items="hoursArray" item-title="title" :rules="[requiredValidator]"
                  item-value="value" label="Start Time" @update:model-value="test" />
              </VCol>

               <VCol cols="12">
                  <VSelect v-model="endTime" :items="hoursArray" item-title="title" :rules="[requiredValidator]"
                    item-value="value" label="End Time" @update:model-value="test" />
                </VCol>
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
