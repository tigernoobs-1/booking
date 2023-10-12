<script setup lang="ts">
import BookingList from '@/views/apps/booking/BookingList.vue';

</script>

<template>
  <div>
    <div class="d-flex align-center mb-6">
      <VAvatar rounded color="primary" variant="tonal" class="me-3" size="large">
        <VIcon :size="32" icon="tabler-calendar" />
      </VAvatar>

      <div>
        <h6 class="text-h6">
          Booking
        </h6>
        <span class="text-sm">Book your space now!</span>
      </div>
    </div>
    <VCard style="z-index: auto;">
      <BookingList/>
    </VCard>
  </div>
</template>
