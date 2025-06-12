// plugins/fetchConfig.client.ts
export default defineNuxtPlugin(async (nuxtApp) => {
  try {
    const res = await nuxtApp.$api('/configs', {
      baseURL: useRuntimeConfig().public.apiBaseUrl
    })

    const configData = {}
    res.forEach(item => {
      configData[item.config_key] = item.config_value
    })

    // ⛳ Gán vào useState đúng tên
    useState('configs', () => configData)
    console.log('✅ Plugin fetchConfig loaded:', configData)
  } catch (err) {
    console.error('❌ Fetch config error:', err)
  }
})
