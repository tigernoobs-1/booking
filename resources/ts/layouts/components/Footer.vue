<template>
  <div class="h-100 d-flex align-center justify-space-between">
    <!-- 👉 Footer: left content -->
    <span class="d-flex align-center">
      &copy;
      {{ new Date().getFullYear() }}
      Direkacipta oleh
      <VIcon
        icon="tabler-heart"
        color="error"
        size="1.25rem"
        class="mx-1"
      />
      <a
        href="https://www.commercedc.com.my/"
        target="_blank"
        rel="noopener noreferrer"
        class="text-primary ms-1"
      >Commerce Dot Com Sdn Bhd</a>
    </span>
    <!-- 👉 Footer: right content -->
    <span class="d-md-flex gap-x-4 text-primary d-none">
      <!-- <a
        href="https://pixinvent.com/demo/vuexy-vuejs-admin-dashboard-template/documentation/"
        target="noopener noreferrer"
      >Documentation</a> -->
    </span>
  </div>
</template>
