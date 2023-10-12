<script setup lang="ts">
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import AddNewItemForm from '@/views/apps/workflow/form/AddNewItemForm.vue'
import { VForm } from 'vuetify/components'
import { requiredValidator } from '@validators'
import type { ServiceRequest } from '@/views/apps/admin/types'
import { useApi } from '@/plugins/axios';
import { Ref } from 'vue'
interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'facilityData', value: ServiceRequest): void
  (e: 'updateData', value: ServiceRequest): void
}

interface PurchasedProduct {
  title: string
  cost: number
  hours: number
  description: string
}
interface Props {
  isDrawerOpen: boolean
  title?: string
  data: { name: string; description: Text; status: boolean, purchasedProducts: PurchasedProduct[] }
  role?: string
}


interface TestItem {
  item: string,
  price: Number,
  quantity: Number

}
const statusList = [
  'Work In Progress',
  'Pending In Replacement Part',
  'Closed'
]


const props = defineProps<Props>()


const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref<VForm>()
const company = ref()
const phone = ref()
const location = ref()
const description = ref()
const componentList = ref()
const selected = ref([])
const serviceStatus = ref()
const userID = ref()
const adminUpdateID = ref()
const adminClosedID = ref()
const testItem: Ref<TestItem[]> = ref([])
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
      //console.log(testItem.value)
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
        flow_type: 'service_request',
        company: company.value,
        location: location.value,
        phone: phone.value,
        component_id: jsonData,
        request_description: description.value,
        status: serviceStatus.value,
        item: testItem.value
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
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
  })
}


const getGroupConfig = async () => {
  const api = useApi()
  const { data } = await api.get('component')
  componentList.value = data
  //console.log(componentList.value)
}

const setData = () => {
  //console.log(props.data)
  if (props.data) {
    company.value = props.data.company
    location.value = props.data.location
    phone.value = props.data.phone
    selected.value = props.data.component_id
    description.value = props.data.details
    userID.value = props.data.user_created.id
    serviceStatus.value = props.data.status
    if (props.data.service_item.length > 0) {
      testItem.value = []
      props.data.service_item.forEach(e => {
        testItem.value.push(e)
      });

    }
    else {
      testItem.value = []
    }
    //console.log('testItemArray', testItem.value)


  }
  else {
    selected.value = []
    refForm.value?.reset()
    refForm.value?.resetValidation()
    const userData = JSON.parse(localStorage.getItem('userData'))
    const user_id = userData.user_id
    userID.value = user_id
    serviceStatus.value = 'New'
    console.log(selected.value)
  }
  /* if (props.data) {
    facilityName.value = props.data.name
    facilityDescription.value = props.data.description
    if (props.data.status.toString() === '1')
      facilityStatus.value = 'Active'
    else
      facilityStatus.value = 'Disable'
  } */
}


// 👉 Add item function
const addItem = () => {
  // eslint-disable-next-line vue/no-mutating-props
  testItem.value.push({
    item: '',
    price: 0,
    quantity: 0,
  })

}

// 👉 Remove Product edit section
const removeProduct = (id: number) => {
  // eslint-disable-next-line vue/no-mutating-props
  testItem.value.splice(id, 1)
}

const addNewItem = (e) => {
  testItem.value.push(e)
  //console.log(testItem.value)
}
onMounted(() => {
  watch(props, async () => {
    await getGroupConfig()
    setData()
  })
})


</script>

<template>
  <VNavigationDrawer temporary :width="900" location="end" class="scrollable-content" :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate">
    <!-- 👉 Title -->
    <div class="d-flex align-center  pa-6 pb-1">
      <h6 class="text-h6">
        {{ props.title }} Service Request
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
              <!-- <VCol cols="6">
                <VTextField v-model="facilityName" :rules="[requiredValidator]" label="Room Type" />
              </VCol> -->

              <!-- 👉 Name -->
              <!-- <VCol cols="6">
                <VTextField v-model="facilityName" :rules="[requiredValidator]" label="W/O Type" />
              </VCol> -->

              <!-- 👉 Name -->
              <VCol cols="6">
                <VTextField v-model="company" :rules="[requiredValidator]" label="Department / Company" />
              </VCol>

              <VCol cols="6">
                <VTextField v-model="phone" :rules="[requiredValidator]" label="Phone" />
              </VCol>

              <!-- 👉 Name -->
              <VCol cols="12">
                <VTextField v-model="location" :rules="[requiredValidator]" label="Location / Level" />
              </VCol>

              <VCol cols="12">
                <VRow>
                  <VCol cols="4" v-for="item in componentList">
                    <VCheckbox v-model="selected" :label="item.title" :value="item.id" :key="selected" />
                  </VCol>
                </VRow>

              </VCol>
              <!-- 👉 Description -->
              <VCol cols="12">
                <VTextarea v-model="description" :rules="[requiredValidator]" label="Request Description" auto-grow />
              </VCol>

              <VCol cols="12" v-if="props.role === 'admin'">
                <VSelect v-model="serviceStatus" :items="statusList" :rules="[requiredValidator]" label="Status" />
              </VCol>

              <VCol class="add-products-form" v-if="props.role === 'admin'">
                <div v-for="(product, index) in testItem" class="ma-sm-4">
                  <AddNewItemForm :id="index" :data="product" @remove-product="removeProduct" @add-item="addNewItem" />
                </div>

                <div class="mt-4 ma-sm-4">
                  <VBtn @click="addItem">
                    Add Item
                  </VBtn>
                </div>
              </VCol>

              <!-- 👉 Status -->
              <!--  <VCol cols="12">
                <VSwitch v-model="facilityStatus" :label="facilityStatus" true-value="Active" false-value="Disable" />
              </VCol> -->

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
