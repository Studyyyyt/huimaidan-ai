import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

export const useLocationStore = defineStore(
  'location',
  () => {
    // 地理位置信息
    const province = ref('')
    const city = ref('')
    const cityId = ref<number | null>(null)
    const district = ref('')
    const address = ref('')

    // 经纬度（用于距离筛选）
    const latitude = ref<number | null>(null)
    const longitude = ref<number | null>(null)

    // 计算属性：完整地址
    const fullAddress = computed(() => {
      if (province.value && city.value && district.value) {
        return `${province.value} ${city.value} ${district.value}`
      }
      return ''
    })

    // 是否已选择位置
    const hasLocation = computed(() => {
      return !!(province.value && city.value && district.value)
    })

    // 是否有经纬度
    const hasCoordinates = computed(() => {
      return latitude.value !== null && longitude.value !== null
    })

    // 设置位置信息
    function setLocation(newProvince: string, newCity: string, newDistrict: string, exactAddress?: string, newCityId?: number | null) {
      province.value = newProvince
      city.value = newCity
      if (newCityId !== undefined) {
        cityId.value = newCityId && Number.isFinite(Number(newCityId)) ? Number(newCityId) : null
      }
      district.value = newDistrict
      address.value = exactAddress || `${newProvince} ${newCity} ${newDistrict}`
    }

    // 设置经纬度
    function setCoordinates(lat: number, lng: number) {
      latitude.value = lat
      longitude.value = lng
    }

    // 单独清除经纬度，保留用户选择的省市区展示。
    function clearCoordinates() {
      latitude.value = null
      longitude.value = null
    }

    // 清除位置信息
    function clearLocation() {
      province.value = ''
      city.value = ''
      cityId.value = null
      district.value = ''
      address.value = ''
      clearCoordinates()
    }

    return {
      province,
      city,
      cityId,
      district,
      address,
      latitude,
      longitude,
      fullAddress,
      hasLocation,
      hasCoordinates,
      setLocation,
      setCoordinates,
      clearCoordinates,
      clearLocation,
    }
  },
  {
    // 持久化配置
    persist: true,
  },
)
