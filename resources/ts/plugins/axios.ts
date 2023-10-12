import { useUserSession } from '@/pages/userStore'
import axios, { AxiosInstance } from 'axios'

const axiosIns = axios.create({
  // You can add your headers here
  // ================================
  // timeout: 1000,
  headers: { 'Content-Type': 'multipart/form-data' },
  baseURL: '/api/',

})

let api: AxiosInstance

export function createApi() {
  // Here we set the base URL for all requests made to the api
  api = axios.create({
    baseURL: '/api/',
  })

  api.interceptors.request.use(config => {
    const userSession = useUserSession()
    if (userSession.token !== null)
      config.headers && (config.headers['Authorization'] = `Bearer ${userSession.token}`);

    return config
  })

  return api
}

export function useApi() {
  if (!api)
    createApi()

  return api
}

export default axiosIns
