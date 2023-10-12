<script setup lang="ts">
import moment from 'moment';
import { useFacilityListStore } from '@/views/apps/admin/useFacilitiesStore';

interface Props {
  isDrawerOpen: boolean
  title?: string
  data: { name: string; description: Text; status: boolean; type: string }
  role?: string
}

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void

}
const props = defineProps<Props>()
const emit = defineEmits<Emit>()
const facilityListStore = useFacilityListStore()

const checkData = () => {
  console.log(props.data)
  //const type = props.data.type
}

const downloadFile = async (e, g) => {
  const downloadData = {
    id: e,
  }
  console.log(g)

  const { data } = await facilityListStore.downloadFile(downloadData);
  const mimeTypes: { [key: string]: string } = {
    '.jpeg': 'image/jpeg',
    '.jpg': 'image/jpeg',
    '.png': 'image/png',
    '.pdf': 'application/pdf',
    '.xls': 'application/vnd.ms-excel',
    '.mp4': 'video/mp4'
    // ... Add more as needed
  };
  const extension = `.${g.split('.').pop()}`;
  const mimeType = mimeTypes[extension];
  const url = URL.createObjectURL(
    new Blob([data], {
      type: mimeType,
    })
  )
  const link = document.createElement('a')
  link.href = url
  link.setAttribute('download', g)
  document.body.appendChild(link)
  link.click()


}

const closePanel = () => {
  emit('update:isDrawerOpen', false)

}

onMounted(() => {
  watch(props, async () => {
    checkData()
  })
})


</script>

<template>
  <VDialog :model-value="props.isDrawerOpen" persistent class="v-dialog-xl">
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="closePanel" />

    <!-- Dialog Content -->
    <VCard title="Details">
      <section cols="12">
        <VRow>
          <VCol cols="12" md="9">
            <VCard>
              <!-- SECTION Header -->
              <VCardText class="d-flex flex-wrap justify-space-between flex-column flex-sm-row print-row">
                <!-- 👉 Left Content -->
                <div class="ma-sm-4">
                  <div class="d-flex align-center mb-6">
                    <!-- 👉 Title -->
                    <h6 class="font-weight-bold text-xl">
                      <!-- {{ type }} -->
                    </h6>
                  </div>
                  <!-- 👉 Address -->
                  <p class="mb-0">
                    <span>Room Type: </span>
                    <span class="font-weight-semibold"> {{ props.data.room_type }} </span>
                  </p>
                  <p class="mb-0">
                    <span>W/O Type: </span>
                    <span class="font-weight-semibold"> {{ props.data.WO_type }} </span>
                  </p>
                  <p class="mb-0">
                    <span>Location: </span>
                    <span class="font-weight-semibold"> {{ props.data.location }} </span>
                  </p>
                  <p class="mb-0">
                    <span>Department: </span>
                    <span class="font-weight-semibold"> {{ props.data.company }} </span>
                  </p>
                  <p class="mb-0">
                    <span>Contact: </span>
                    <span class="font-weight-semibold"> +06 {{ props.data.phone }} </span>
                  </p>
                </div>
                <!-- 👉 Right Content -->
                <div class="mt-4 ma-sm-4">
                  <!-- 👉 Invoice ID -->
                  <h6 class="font-weight-medium text-xl mb-6">
                    {{ props.data.number }}
                  </h6>

                  <!-- 👉 Issue Date -->
                  <p class="mb-2">
                    <span>Date Issued: </span>
                    <span class="font-weight-semibold"> {{ moment(props.data.created).format("DD/MM/YYYY h:mm A")
                    }}</span>
                  </p>

                  <!-- 👉 Due Date -->
                  <p class="mb-2">
                    <span>Due Date: </span>
                    <span class="font-weight-semibold"></span>
                  </p>
                </div>
              </VCardText>
              <!-- !SECTION -->
              <VDivider />
              <VCardText class="d-flex justify-space-between flex-wrap flex-column flex-sm-row print-row">
                <VCol cols="12">
                  <div class="d-flex align-center mb-6">
                    <!-- 👉 Title -->
                    <h6 class="font-weight-bold text-sm">
                      LIST ITEM
                    </h6>
                  </div>
                  <VRow>
                    <VCol cols="2" v-for="item in props.data.component">
                      {{ item }}
                    </VCol>
                  </VRow>
                </VCol>
              </VCardText>
              <VDivider />
              <VCardText class="d-flex justify-space-between flex-wrap flex-column flex-sm-row print-row">
                <div class="ma-sm-4">
                  <h6 class="text-sm font-weight-semibold mb-3">
                    Contact Reason:
                  </h6>
                  <p class="mb-1">
                    {{ props.data.contact_reason }}
                  </p>
                </div>
              </VCardText>
              <!-- 👉 Table -->
              <VDivider />
              <VCardText>
                <div class="d-flex mx-sm-4">
                  <h6 class="text-sm font-weight-semibold me-1">
                    Report:
                  </h6>
                  <span>{{ props.data.report }}</span>
                </div>
              </VCardText>
              <VDivider />
              <VCardText class="d-flex justify-space-between flex-wrap flex-column flex-sm-row print-row">
                <VCol cols="12">
                  <div class="d-flex align-center mb-6">
                    <!-- 👉 Title -->
                    <h6 class="font-weight-bold text-sm">
                      ATTACHMENT FILE
                    </h6>
                  </div>
                  <VRow>
                    <VCol cols="3" v-for="item in props.data.attachment_file">

                      <a @click="downloadFile(item.id, item.file_name)">
                        <VIcon size="22" icon="tabler-paperclip" /> {{ item.file_name }}
                      </a>
                    </VCol>
                  </VRow>
                </VCol>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </section>
      <VCardText class="d-flex justify-end gap-3 flex-wrap">
        <VBtn color="secondary" variant="tonal" @click="isShowDetails = !isShowDetails">
          Close
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>
