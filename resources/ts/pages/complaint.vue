<script setup lang="ts">
import { useApi } from '@/plugins/axios'
import { isUserLoggedIn } from '@/router/utils'
import ServiceRequestList from '@/views/apps/workflow/ServiceRequestList.vue'
import WorkOrderList from '@/views/apps/workflow/WorkOrderList.vue'
import { VForm } from 'vuetify/components'

const isLoggedIn = isUserLoggedIn()
const email = ref()
const showFacility = ref(false)
const typeFacility = ref()
const formType = ref()
const details = ref()
const loadings = ref<boolean[]>([])
const items: unknown[] = []
const type = ['Complaint', 'Suggestion']
const refForm = ref<VForm>()
const showSnackBar = ref(false)
const message = ref('')
const currentTab = ref(0)

const mobile = ref<number>()
const password = ref<string>()
const checkbox = ref(false)

if (isLoggedIn) {
  const userdata = JSON.parse(localStorage.getItem('userData') || '{}')

  email.value = userdata.email
}

const setFacility = async () => {
  const api = useApi()
  const { data } = await api.get('item')
  const list = data.data.filter((item: { status: number }) => item.status === 1);
  for (let i = 0; i < list.length; i++)
    items.push(list[i].name)
}

const typeEvent = (e: string) => {
  if (e === 'Complaint')
    showFacility.value = true
  else
    showFacility.value = false
  typeFacility.value = ''
}

async function submitApi(fdata: any, e: number) {
  const api = useApi()
  const { data } = await api.post('complain', fdata)
  if (data) {
    message.value = data.message
    showSnackBar.value = true
    loadings.value[e] = false
    refForm.value?.reset()
  }
}

const submitForm = (i: number) => {
  refForm.value?.validate()
    .then(async ({ valid }) => {
      if (valid) {
        loadings.value[i] = true

        const formData = {
          user: email.value,
          type: formType.value,
          details: details.value,
          facility: typeFacility.value,
        }

        await submitApi(formData, i)
      }
    })
}

onMounted(() => {
  setFacility()
  showSnackBar.value = false
})
</script>

<template>
  <div>
    <VTabs v-model="currentTab" class="v-tabs-pill">
      <VTab>Work Order</VTab>
      <VTab>Service Request</VTab>
    </VTabs>

    <VCard class="mt-5" style="z-index: auto;">
      <VCardText>
        <VWindow v-model="currentTab">
          <VWindowItem>
            <WorkOrderList />
          </VWindowItem>
          <VWindowItem>
            <ServiceRequestList />
          </VWindowItem>
        </VWindow>
      </VCardText>
    </VCard>
  </div>
</template>
