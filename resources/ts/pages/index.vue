
<script setup lang="ts">
import { useApi } from '@/plugins/axios';
// import sliderBar1 from '@images/banner/badminton.png';
// import sliderBar2 from '@images/illustrations/sidebar-pic-2.png';
// import sliderBar3 from '@images/illustrations/sidebar-pic-3.png';
import moment from 'moment';
import { VIcon } from 'vuetify/components';

const isDialogVisible = ref(false)
const dialogTitle = ref()
const dialogDesc = ref()
const notices = ref()

const setNotice = async () => {
  const api = useApi()
  const { data } = await api.get('notice')

  const filteredNotices = data.data.filter((notice: { status: number; }) => notice.status === 1)
  notices.value = filteredNotices

  console.log(data)
}

const dialogClick = (notice: { title: any; content: any; }) => {
  isDialogVisible.value = true
  dialogTitle.value = notice.title
  dialogDesc.value = notice.content
}

// const sliderBar1  = '/images/banner/badminton.png'
const sliderBar1 = new URL('@images/banner/badminton.png', import.meta.url).href
const sliderBar2 = new URL('@images/banner/futsal.png', import.meta.url).href
const sliderBar3 = new URL('@images/banner/facility.png', import.meta.url).href

onMounted(() => {
  setNotice()
})

const sliderImages = [
  {
    name: 'Badminton',
    slideImg: sliderBar1,
  },
  {
    name: 'Futsal',
    slideImg: sliderBar2,
  },
  {
    name: 'Facility',
    slideImg: sliderBar3,
  },
]
</script>

<template>
  <div>
    <VCard color="primary" style="margin-bottom: 20px;">
      <VCarousel
      cycle
      :continuous="false"
      :show-arrows="false"
      hide-delimiter-background
      :delimiter-icon="() => h(VIcon, { icon: 'fa-circle', size: '10' })"
      height="auto"
      class="carousel-delimiter web-analytics-carousel"
    >
      <VCarouselItem
        v-for="item in sliderImages"
        :key="item.name"
      >
        <VCardText style="padding: 0;">
          <VRow style="margin: -20px;">
            <VCol
              cols="12"
              sm="6"
              order="1"
              order-sm="2"
              class="position-relative text-center"
            >
              <img
                :src="item.slideImg"
                class="card-slider-img"
              >
            </VCol>
          </VRow>
        </VCardText>
      </VCarouselItem>
    </VCarousel>
    </VCard>
    <div class="d-flex align-center mb-6">
      <VAvatar rounded color="primary" variant="tonal" class="me-3" size="large">
        <VIcon :size="32" icon="tabler-notification" />
      </VAvatar>

      <div>
        <h6 class="text-h6">
          Notice
        </h6>
        <span class="text-sm">Welcome, here some information for you...</span>
      </div>
    </div>
    <VList lines="two" border>
      <template v-for="(notice, index) of notices" :key="notice.title">
        <VListItem>
          <template #prepend>
            <VIcon
              icon="tabler-info-square"
              start
              size="28"
              color="info"
            />
          </template>
          <VListItemTitle>
            {{ notice.title }} <VChip><span class="text-xs text-disabled">{{ moment(notice.date_created).format('DD-MM-YYYY') }}</span></VChip>
          </VListItemTitle>
          <VListItemSubtitle class="mt-1" style="padding-right: 20em; -webkit-line-clamp: 3;">
            <span class="text-subtitle-2" v-html="notice.content"></span>
          </VListItemSubtitle>

          <template #append>
            <VBtn size="small" @click="dialogClick(notice)">
              More
            </VBtn>
          </template>
        </VListItem>
        <VDivider v-if="index !== notices.length - 1" />
      </template>
    </VList>

    <VDialog v-model="isDialogVisible" width="1080">
      <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" />
      <VCard :title="dialogTitle">
        <VCardText v-html="dialogDesc"></VCardText>
        <VCardText class="d-flex justify-end flex-wrap gap-3">
          <VBtn @click="isDialogVisible = false">
            Ok
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>

<style lang="scss" scoped>
// .card-slider-img {
//   block-size: auto;
//   inline-size: 100%;
// }

</style>
