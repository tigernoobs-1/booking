<script setup lang="ts">
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { VForm } from 'vuetify/components'
import { requiredValidator } from '@validators'
import type { UserProperties } from '@/views/apps/admin/types'
import userRole from '@/config/roleConfig'
import { useApi } from '@/plugins/axios';

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'updateData', value: UserProperties): void
}

interface Props {
  isDrawerOpen: boolean
  title?: string
  data: { name: string; description: Text; role: string, group: string, is_active:number }
}


const props = defineProps<Props>()

const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref<VForm>()
const facilityName = ref()
const facilityDescription = ref()
const selectedRole = ref()
const userGroup = ref()
const selectedGroup = ref()
const listRole = ref()
const userStatus = ref()
listRole.value = Object.values(userRole)
console.log(listRole.value)
// const status = ref()
//console.log('userRole', userRole)
//console.log(Object.values(userRole))

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
      if (userStatus.value === 'Active')
        userStatus.value = true
      else
        userStatus.value = false
      const formData = {
        role: selectedRole.value,
        user_group: selectedGroup.value,
        is_active: userStatus.value
      }

      emit('updateData', formData)

      emit('update:isDrawerOpen', false)
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
      })
    }
  })
}

const getGroupConfig = async () => {
  const api = useApi()
  /* const { data } = await api.get('user-group') */
  const { data } = await api.get('company-list')
  userGroup.value = [...new Set(data.data.map((item: { company_group: any }) => item.company_group))];
  console.log(userGroup.value)
}
const handleDrawerModelValueUpdate = (val: boolean) => {
  emit('update:isDrawerOpen', val)
}

const setData = () => {
  if (props.data) {
    console.log(props.data)
    facilityName.value = props.data.name
    facilityDescription.value = props.data.description
    selectedRole.value = props.data.role
    selectedGroup.value = props.data.group
    if (props.data.is_active === 1) {
      userStatus.value = 'Active'
    }
    else {
      userStatus.value = 'Disble'
    }
    //userStatus.value = props.data.is_active
  }
}

onMounted(() => {
  getGroupConfig()
  watch(props, () => {
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
        {{ props.title }} User
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
                <VTextField v-model="facilityName" :rules="[requiredValidator]" label="User Name" />
              </VCol>
              <VCol cols="12">
                <VSelect v-model="selectedRole" label="User Role" :items="listRole" clearable
                  clear-icon="tabler-x"  item-title="description" item-value="id"/>

              </VCol>
              <!-- 👉 Status -->
              <VCol cols="12">
                <!-- <VSwitch v-model="userRole" :label="userRole" true-value="Admin" false-value="user" /> -->
                <VSelect v-model="selectedGroup" label="Select Group" :items="userGroup" clearable
                  clear-icon="tabler-x" />
              </VCol>

               <!-- 👉 Status -->
              <VCol cols="12">
                <VSwitch v-model="userStatus" :label="userStatus" true-value="Active" false-value="Disable" />
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
