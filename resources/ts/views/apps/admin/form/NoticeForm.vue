<script setup lang="ts">
import type { NoticeProperties } from '@/views/apps/admin/types';
import { requiredValidator } from '@validators';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';
import { VForm } from 'vuetify/components';


interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'facilityData', value: NoticeProperties): void
  (e: 'updateData', value: NoticeProperties): void
}

interface Props {
  isDrawerOpen: boolean
  title?: string
  data: { title: string; content: Text; status: boolean }
}

const props = defineProps<Props>()

const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref<VForm>()

const noticeDesc = ref()
const noticeStatus = ref()
const noticeTitle = ref()
const quilEditor = ref()
// const status = ref()

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)

  nextTick(() => {
    //resetValue.value = true
    refForm.value?.reset()
    refForm.value?.resetValidation()
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      const statusValue = ref()
      if (noticeStatus.value === 'Active')
        statusValue.value = true
      else
        statusValue.value = false

      const formData = {
        title: noticeTitle.value,
        content: noticeDesc.value,
        status: statusValue.value,
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
  //resetValue.value = false
  if (props.data) {
    console.log(props.data)
    noticeTitle.value = props.data.title
    noticeDesc.value = props.data.content
    if (props.data.status.toString() === '1')
      noticeStatus.value = 'Active'
    else
      noticeStatus.value = 'Disable'
  }

  else {
    console.log('masuk')
    //noticeDesc.value = newData.value
    refForm.value?.reset()
    noticeDesc.value = ''
    //noticeTitle.value = ''
    noticeStatus.value = 'Disable'
    quilEditor.value.setContents('')

  }
}

/* TEST ONLY */
watch(noticeDesc, (newValue) => {
  console.log('notice description value changed:', newValue);
});

onMounted(() => {
  watch(props, () => {
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
        {{ props.title }} Notice
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
                <VTextField v-model="noticeTitle" :rules="[requiredValidator]" label="Title" />
              </VCol>

              <!-- 👉 Description -->
              <VCol cols="12" class="mb-2">
                <VLabel>Description :</VLabel>
                <QuillEditor ref="quilEditor" v-model:content="noticeDesc" :rules="[requiredValidator]" contentType="html"
                  theme="snow" style="height: 250px;" />
              </VCol>
              <!-- 👉 Status -->
              <VCol cols="12">
                <VSwitch v-model="noticeStatus" :label="noticeStatus" true-value="Active" false-value="Disable" />
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



