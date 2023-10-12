
import type { BookingProperties, filterProperties } from '@/views/apps/booking/types';
interface filtering {
  name:string,
  facility : {
    name: string
  }
}
export function filterDataTest(responseData: any, searchQuery: string, currentPage: number, totalPage: number, rowPerPage: number, dataFilter: any) {
  const facilitiesData = ref()
  const facilities = responseData
  const queryLower = searchQuery.toLowerCase()
  let filteredFacilities = ref()
  if (dataFilter === 'booking') {
    filteredFacilities.value = responseData.filter((booking: filtering) => ((booking.facility.name.toLowerCase().includes(queryLower)))).reverse()
  }
  if (dataFilter === 'facility') {
    filteredFacilities.value = responseData.filter((facility: filtering) => ((facility.name.toLowerCase().includes(queryLower)))).reverse()
  }
  if (dataFilter === 'user') {
    filteredFacilities.value = responseData.filter((user: filtering) => ((user.name.toLowerCase().includes(queryLower)))).reverse()
  }

  totalPage = Math.ceil(filteredFacilities.value.length / rowPerPage) ? Math.ceil(filteredFacilities.value.length / rowPerPage) : 1
  if (rowPerPage) {
    const firstIndex = (currentPage - 1) * rowPerPage
    const lastIndex = rowPerPage * currentPage
    filteredFacilities = filteredFacilities.value.slice(firstIndex, lastIndex)
  }

  facilitiesData.value = {
    facility: filteredFacilities,
    page: totalPage
  }

  console.log(facilitiesData)

  return facilitiesData.value
  

}
