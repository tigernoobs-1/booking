import type { HorizontalNavItems } from '@layouts/types';

const commonMenuItems = [
  {
    title: 'Home',
    to: { name: 'index' },
    icon: { icon: 'tabler-smart-home' },
    action: 'read',
    subject: 'Auth'
  },
  {
    title: 'Booking',
    to: { name: 'booking' },
    icon: { icon: 'tabler-calendar' },
    action: 'read',
    subject: 'Auth'
  },
  {
    title: 'Complaint & Feedback',
    to: { name: 'complaint' },
    icon: { icon: 'tabler-message-report' },
    action: 'read',
    subject: 'Auth'
  },
];


const menuItems = [
  ...commonMenuItems,
  {
    title: 'Administrator',
    to: { name: 'admin' },
    icon: { icon: 'tabler-settings' },
    action: 'read',
    subject: 'admin',
  },
] as HorizontalNavItems;


export default menuItems;
