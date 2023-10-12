export interface BookingParams {
  q: string,
 /*  name: string
  description: string
  capcity: string
  location: string
  status: string,
  perPage: number,
  currentPage: number, */
}



export interface BookingProperties {
  user_id: BigInt,
  item_id: BigInt,
  status: string,
  remarks: Text,
  start_time: string,
  end_time: string,
}

export interface UserProperties {
  role: string
}

export interface filterProperties {
  facility: {
    user: any; name: string;
  }
}
