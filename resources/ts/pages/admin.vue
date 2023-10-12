<route lang="yaml">
meta:
  action: read
  subject: admin
</route>
<script setup lang="ts">
import userRole from '@/config/roleConfig';
import AdminBooking from '@/views/apps/admin/AdminBooking.vue';
import AdminComplaint from '@/views/apps/admin/AdminComplaint.vue';
import AdminDayConfig from '@/views/apps/admin/AdminDayConfig.vue';
import AdminFacility from '@/views/apps/admin/AdminFacility.vue';
import AdminNotice from '@/views/apps/admin/AdminNotice.vue';
import AdminTimeConfig from '@/views/apps/admin/AdminTimeConfig.vue';
import AdminUserMgmtVue from '@/views/apps/admin/AdminUserMgmt.vue';
import AdminWorkOrder from '@/views/apps/admin/AdminWorkOrder.vue';

const currentTab = ref(0)
const currentUserRole = ref()

const userData = JSON.parse(localStorage.getItem('userData') || '{}');
currentUserRole.value = userData.role;
console.log('currentUserRole', currentUserRole.value)
console.log('userRole', userRole.superAdmin.id)

const subMenu = [
  {
    name: 'Facility',
    icon: 'tabler-home',
    role: [userRole.superAdmin.id, userRole.sportAdmin.id],
    component: AdminFacility
  },
  {
    name: 'Booking',
    icon: 'tabler-calendar',
    role: [userRole.superAdmin.id, userRole.sportAdmin.id],
    component: AdminBooking
  },
  {
    name: 'Service Request',
    icon: 'tabler-brand-twitch',
    role: [userRole.superAdmin.id, userRole.facilityAdmin.id],
    component: AdminComplaint
  },
  {
    name: 'Work Order',
    icon: 'tabler-brand-twitch',
    role: [userRole.superAdmin.id, userRole.facilityAdmin.id],
    component: AdminWorkOrder
  },
  {
    name: 'User Management',
    icon: 'tabler-user',
    role: [userRole.superAdmin.id],
    component: AdminUserMgmtVue
  },
  {
    name: 'Notice Management',
    icon: 'tabler-urgent',
    role: [userRole.superAdmin.id, userRole.facilityAdmin.id, userRole.sportAdmin.id],
    component: AdminNotice
  },
  {
    name: 'Operation Hour',
    icon: 'tabler-clock-cog',
    role: [userRole.superAdmin.id, userRole.facilityAdmin.id, userRole.sportAdmin.id],
    component: AdminTimeConfig
  },
  {
    name: 'Day Group',
    icon: 'tabler-calendar-cog',
    role: [userRole.superAdmin.id, userRole.facilityAdmin.id, userRole.sportAdmin.id],
    component: AdminDayConfig
  },
]

console.log('subMenu', subMenu)

console.log('currentTab', currentTab.value)

</script>

<template>
  <VCard style="z-index: auto;">
    <VCardText>
      <VRow>
        <VCol cols="12" lg="3" md="4" sm="12">
          <VTabs v-model="currentTab" direction="vertical">
            <template v-for="(item, index) in subMenu">
              <VTab :key="index" v-if="item.role.includes(currentUserRole)">
                <VIcon start :icon="item.icon" />
                {{ item.name }}
              </VTab>
            </template>
          </VTabs>
        </VCol>

        <VDivider v-if="$vuetify.display.smAndDown" horizontal />
        <VDivider v-else vertical />

        <VCol cols="12" lg="9" md="8" sm="12">
          <VWindow v-model="currentTab" class="ms-3">
            <template v-for="(item, index) in subMenu">
              <VWindowItem :key="index" v-if="item.role.includes(currentUserRole)">
                <component :is="item.component" />
              </VWindowItem>
            </template>
          </VWindow>
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>


