import { DateTimeFormat } from "@intlify/core-base"

export interface FacilityParams {
  q: string,
 /*  name: string
  description: string
  capcity: string
  location: string
  status: string,
  perPage: number,
  currentPage: number, */
}



export interface FacilityProperties {
  name: string
  description: Text
  status: boolean
  maximum_time: Number
  limit_user: Number
}

export interface NoticeProperties {
  title: string
  content: string
  status: status
}

export interface UserProperties {
  role: string,
  user_group: string
}


export interface BookingProperties {
  user_id: BigInt,
  item_id: BigInt,
  status: string,
  remarks: Text,
  start_time: string,
  end_time: string,
}


export interface ConfigTime {
  is_active :boolean,
  config_value: any
}


export interface ServiceRequest {
  user_created_id: number,
  flow_type: string,
  company: string,
  location: string,
  phone: string,
  component_id: any,
  request_description: string,
  status: string,
}


export interface WorkOrder {
  user_created_id: number,
  flow_type: string,
  room_type: string,
  WO_type: string,
  company: string,
  location: string,
  phone: string,
  component_id: any,
  status: string,
  contact_reason: string,
}
