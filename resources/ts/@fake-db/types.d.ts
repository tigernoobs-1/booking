// SECTION App: Calendar
export interface CalendarEvent {
  id: string
  title: string
  start: string
  end: string
  allDay: boolean
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  extendedProps: Record<string, any>
}
// !SECTION

// SECTION App: User
export interface UserProperties {
  id: number
  fullName: string
  company: string
  role: string
  country: string
  contact: string
  email: string
  currentPlan: string
  status: string
  billing:string
  avatar: string
}
// !SECTION

// SECTION App: User
export interface Facilities {
  id: number
  name: string
  description: string
  capcity: string
  location: string
  createdAt: string
  updatedAt: string
}
// !SECTION
