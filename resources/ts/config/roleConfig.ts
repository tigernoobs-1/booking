interface UserRole {
  [key: string]: {
    id: string;
    description: string;
  };
}

const userRole: UserRole = {
  user: {
    id: 'user',
    description: 'User'
  },
  superAdmin: {
    id: 'Admin',
    description: 'Super Admin'
  },
  sportAdmin: {
    id: 'admin-sport',
    description: 'Sports Admin'
  },
  facilityAdmin: {
    id: 'admin-facility',
    description: 'Facility Admin'
  }
};

export default userRole;
