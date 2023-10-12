<script setup lang="ts">
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { VForm } from 'vuetify/components'
import { requiredValidator } from '@validators'
import type { WorkOrder } from '@/views/apps/admin/types'
import { useApi } from '@/plugins/axios';
import { useFacilityListStore } from '@/views/apps/admin/useFacilitiesStore';

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'facilityData', value: WorkOrder): void
  (e: 'updateData'): void
  (e: 'fetchData'): void
}

interface Props {
  isDrawerOpen: boolean
  title?: string
  data: { name: string; description: Text; status: boolean }
  role?: string
}

const statusList = [
  'Work In Progress',
  'Pending For Approval (Financial)',
  'Closed'
]


const roomTypeList = [
  'Room Type 1',
  'Room Type 2',
  'Room Type 3',
  'Room Type 4',
  'Room Type 5',
]

const workOrderList = [
  'Work Order 1',
  'Work Order 2',
  'Work Order 3',
  'Work Order 4',
  'Work Order 5',
]


const props = defineProps<Props>()

const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref<VForm>()
const fileForm = ref<VForm>()
const roomType = ref()
const woType = ref()
const location = ref()
const department = ref()
const phone = ref()
const contactReason = ref()
const componentList = ref()
const selected = ref([])
const serviceStatus = ref()
const report = ref()
const adminUpdateID = ref()
const adminClosedID = ref()
const userID = ref()
const inputFile = ref()
const attachmentFile = ref()
const facilityListStore = useFacilityListStore()
const serviceId = ref()
const tab = ref('personal-info')


const getGroupConfig = async () => {
  const api = useApi()
  const { data } = await api.get('component')
  componentList.value = data
}

const formData = {
  user_created_id: 'test',
}

// 👉 drawer close
const closeNavigationDrawer = () => {
  //emit('fetchData')
  //emit('updateData', formData)
  emit('update:isDrawerOpen', false)

  tab.value = 'personal-info'
  refForm.value?.reset()
  refForm.value?.resetValidation()
  nextTick(() => {
    refForm.value?.reset()
    inputFile.value = []
    refForm.value?.resetValidation()
  })
}

const onSubmit = async () => {
  refForm.value?.validate().then(({ valid }) => {
    /* let formData = new FormData() */
    if (valid) {
      const userData = JSON.parse(localStorage.getItem('userData'))
      const user_id = userData.user_id
      const jsonData = JSON.stringify(selected.value)
      if (serviceStatus.value === 'Work In Progress' || serviceStatus.value === 'Pending In Replacement Part') {
        adminUpdateID.value = user_id
      }
      if (serviceStatus.value === 'Closed') {
        adminUpdateID.value = props.data.user_updated.id
        adminClosedID.value = user_id
      }
      const formData = {
        user_created_id: userID.value,
        user_updated_id: adminUpdateID.value,
        user_closed_id: adminClosedID.value,
        flow_type: 'work_order',
        room_type: roomType.value,
        company: department.value,
        location: location.value,
        phone: phone.value,
        component_id: jsonData,
        contact_reason: contactReason.value,
        status: serviceStatus.value,
        report: report.value,
        WO_type: woType.value
      }
      if (valid && props.title === 'Add') {
        console.log(formData)
        facilityListStore.addServiceRequest(formData).then(data => {
          serviceId.value = data.data;
          console.log(serviceId.value);
        })
          .catch(error => {
            console.error("Error:", error);
          });
          emit('updateData')
      }
      

      if (valid && props.title === 'Update') {
        facilityListStore.updateServiceRequest(formData, serviceId.value).then(data => {
          serviceId.value = data.data; 
        })
          .catch(error => {
            console.error("Error:", error);
          });
        emit('updateData')
      }
      if (props.role !== 'admin') {
        tab.value = 'account-details'
      }
      else {
        closeNavigationDrawer()
      }
    }
  })
}

const fileSubmit = async () => {
  let formData = new FormData()
  console.log(inputFile.value.length)
  inputFile.value.forEach((e, index) => {
    formData.append(`file_input[${index}]`, e)
  });
  formData.append('facility_services_id', serviceId.value)
  const api = useApi()
  const { data } = await api.post('store-file', formData)
  attachmentFile.value = data.data
  emit('updateData')
}

const deleteFile = async (e) => {
  const api = useApi()
  const { data } = await api.delete(`delete-file/${e}`)
  attachmentFile.value = data.data
  emit('updateData')
  
}

const handleDrawerModelValueUpdate = (val: boolean) => {
  emit('update:isDrawerOpen', val)
}

const setData = () => {
  if (props.data) {
    console.log(props.data)
    roomType.value = props.data.room_type
    woType.value = props.data.WO_type
    department.value = props.data.company
    phone.value = props.data.phone
    location.value = props.data.location
    selected.value = props.data.component_id
    contactReason.value = props.data.contact_reason
    serviceStatus.value = props.data.status
    userID.value = props.data.user_created.id
    attachmentFile.value = props.data.attachment_file
    serviceId.value = props.data.id
    report.value = props.data.report
  }
  else {
    selected.value = []
    inputFile.value = []
    const userData = JSON.parse(localStorage.getItem('userData'))
    const user_id = userData.user_id
    userID.value = user_id
    serviceId.value = ''
    serviceStatus.value = 'New'
  }
}

const checkEvent = () => {
  console.log()
}

onMounted(() => {
  watch(() => props.isDrawerOpen, async (newValue, oldValue) => {
    // Check if newValue is true
    if (newValue) {
      await getGroupConfig();
      setData();
    }
  });
})

const displayForm = (e: String) => {
  if (e === 'admin') {
    return true
  }

  else {
    return false
  }

}

</script>

<template>
  <VNavigationDrawer temporary :width="700" location="end" class="scrollable-content" :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate">
    <!-- 👉 Title -->
    <div class="d-flex align-center  pa-6 pb-1">
      <h6 class="text-h6">
        {{ props.title }} Work Order
      </h6>

      <VSpacer />

      <!-- 👉 Close btn -->
      <VBtn variant="tonal" color="default" icon size="32" class="rounded" @click="closeNavigationDrawer">
        <VIcon size="18" icon="tabler-x" />
      </VBTn>
    </div>
    <VTabs v-model="tab">
      <VTab value="personal-info">
        Form Details
      </VTab>
      <VTab v-if="serviceId" value="account-details">
        File Atachment
      </VTab>
    </VTabs>
    <VDivider />
    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <VWindow v-model="tab">
            <VWindowItem value="personal-info">

              <VCard flat>
                <VCardText>
                  <!-- 👉 Form -->
                  <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
                    <VRow>
                      <!-- 👉 Name -->
                      <VCol cols="6">
                        <VSelect v-model="roomType" :items="roomTypeList" :rules="[requiredValidator]" label="Room Type"
                          :disabled="displayForm('Newadmin')" />
                      </VCol>

                      <!-- 👉 Name -->
                      <VCol cols="6">
                        <VSelect v-model="woType" :items="workOrderList" :rules="[requiredValidator]" label="W/O Type"
                          :disabled="displayForm('Newadmin')" />
                      </VCol>

                      <!-- 👉 Name -->
                      <VCol cols="6">
                        <VTextField v-model="department" :rules="[requiredValidator]" label="Department"
                          :disabled="displayForm('Newadmin')" />
                      </VCol>

                      <VCol cols="6">
                        <VTextField v-model="phone" :rules="[requiredValidator]" label="Phone"
                          :disabled="displayForm('Newadmin')" />
                      </VCol>

                      <!-- 👉 Name -->
                      <VCol cols="12">
                        <VTextField v-model="location" :rules="[requiredValidator]" label="Location"
                          :disabled="displayForm('Newadmin')" />
                      </VCol>


                      <VCol cols="12">
                        <VRow>
                          <VCol cols="4" v-for="item in componentList">
                            <VCheckbox v-model="selected" :label="item.title" :value="item.id"
                              :disabled="displayForm('Newadmin')" />
                          </VCol>
                        </VRow>

                      </VCol>

                      <!-- 👉 Description -->
                      <VCol cols="12">
                        <VTextarea v-model="contactReason" :rules="[requiredValidator]" label="Contact Reason" auto-grow
                          :disabled="displayForm('Newadmin')" />
                      </VCol>
                      <VCol cols="12" v-if="props.role === 'admin'">
                        <VSelect v-model="serviceStatus" :items="statusList" :rules="[requiredValidator]"
                          label="Status" />
                      </VCol>

                      <!-- 👉 Description -->
                      <VCol cols="12" v-if="props.role === 'admin'">
                        <VTextarea v-model="report" label="Report" auto-grow />
                      </VCol>

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

            </VWindowItem>

            <VWindowItem value="account-details">
              <VForm class="mt-2" ref="fileForm" v-model="isFormValid" @submit.prevent="fileSubmit">
                <VRow>
                  <VCol cols="12" v-if="props.role !== 'admin'">
                    <VFileInput multiple chips v-model="inputFile" label="File input" @change="checkEvent" />
                  </VCol>

                  <VCol cols="12">
                    <VRow>
                      <VCol cols="5" v-for="file in attachmentFile">
                        <VIcon size="22" icon="tabler-file" /> {{ file.file_name }}
                        <VBtn icon size="x-small" color="default" variant="text">
                          <VIcon size="20" icon="tabler-x" @click="deleteFile(file.id)" />
                        </VBtn>
                      </VCol>
                    </VRow>
                  </VCol>
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
            </VWindowItem>
          </VWindow>
        </VCardText>

        <VDivider />
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
