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

const dayList = [
  { value: 0, title: 'Sunday' },
  { value: 1, title: 'Monday' },
  { value: 2, title: 'Tuesday' },
  { value: 3, title: 'Wednesday' },
  { value: 4, title: 'Thursday' },
  { value: 5, title: 'Friday' },
  { value: 6, title: 'Saturday' }

]

const props = defineProps<Props>()

const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref<VForm>()
const facilityStatus = ref('Disable')
const selectedGroup = ref()
const facilityOption = ref()
const selectedDay = ref()
const selectedOptions = ref()
const userGroup = ref()


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
        group: selectedGroup.value,
        facility_id: selectedOptions.value,
        available_day: selectedDay.value
      }

      const jsonData = JSON.stringify(configData)
      const formData = {
        is_active: statusValue.value,
        config_value: jsonData,
        config_name: 'operation_day'
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

const setFacility = async () => {
  const api = useApi()
  const { data } = await api.get('item')
  const list = data.data.filter((obj: { status: number }) => obj.status === 1)
  const listOptions: [{ value: string, title: string }] = []
  for (let i = 0; i < list.length; i++)
    listOptions.push({ value: list[i].id, title: list[i].name })
  //usersArray.push(userList.value[i].name)
  facilityOption.value = listOptions
  console.log(facilityOption.value)
}


const setGroup = async () => {
  const api = useApi()
  const { data } = await api.get('company-list')
  userGroup.value = [...new Set(data.data.map((item: { company_group: any }) => item.company_group))];
}


const setData = () => {
  if (props.data) {

    selectedGroup.value = props.data.config_value.group
    selectedDay.value = props.data.config_value.available_day
    selectedOptions.value = props.data.config_value.facility_id
    if (props.data.status.toString() === '1')
      facilityStatus.value = 'Active'
    else
      facilityStatus.value = 'Disable'
  }
}




onMounted(() => {
  watch(props, () => {
    setFacility()
    setGroup()
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
        {{ props.title }} Group
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
                <!-- <VSwitch v-model="userRole" :label="userRole" true-value="Admin" false-value="user" /> -->
                <VSelect v-model="selectedGroup" label="Select Group" :items="userGroup" clearable
                  clear-icon="tabler-x" />
              </VCol>

              <VCol cols="12">
                <VSelect v-model="selectedOptions" :items="facilityOption" :menu-props="{ maxHeight: '400' }"
                  label="Facility" item-value="value" multiple persistent-hint />

              </Vcol>
              <!-- 👉 Max Time -->
              <VCol cols="12">
                <VSelect v-model="selectedDay" :items="dayList" :menu-props="{ maxHeight: '400' }" label="Available Day"
                  item-value="value" multiple persistent-hint />
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
