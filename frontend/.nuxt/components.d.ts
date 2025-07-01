
import type { DefineComponent, SlotsType } from 'vue'
type IslandComponent<T extends DefineComponent> = T & DefineComponent<{}, {refresh: () => Promise<void>}, {}, {}, {}, {}, {}, {}, {}, {}, {}, {}, SlotsType<{ fallback: { error: unknown } }>>
type HydrationStrategies = {
  hydrateOnVisible?: IntersectionObserverInit | true
  hydrateOnIdle?: number | true
  hydrateOnInteraction?: keyof HTMLElementEventMap | Array<keyof HTMLElementEventMap> | true
  hydrateOnMediaQuery?: string
  hydrateAfter?: number
  hydrateWhen?: boolean
  hydrateNever?: true
}
type LazyComponent<T> = (T & DefineComponent<HydrationStrategies, {}, {}, {}, {}, {}, {}, { hydrated: () => void }>)
interface _GlobalComponents {
      'Loading': typeof import("../components/Loading.vue")['default']
    'SignaturePad': typeof import("../components/SignaturePad.vue")['default']
    'ForgotPasswordForm': typeof import("../components/layouts/default/auth/ForgotPasswordForm.vue")['default']
    'LoginForm': typeof import("../components/layouts/default/auth/LoginForm.vue")['default']
    'RegisterForm': typeof import("../components/layouts/default/auth/RegisterForm.vue")['default']
    'SearchBanner': typeof import("../components/layouts/default/home/SearchBanner.vue")['default']
    'SectionFeaturedDistricts': typeof import("../components/layouts/default/home/SectionFeaturedDistricts.vue")['default']
    'SectionFeaturedMotels': typeof import("../components/layouts/default/home/SectionFeaturedMotels.vue")['default']
    'FilterWidget': typeof import("../components/layouts/default/listings/FilterWidget.vue")['default']
    'MotelItem': typeof import("../components/layouts/default/listings/MotelItem.vue")['default']
    'Pagination': typeof import("../components/layouts/default/listings/Pagination.vue")['default']
    'RoomItem': typeof import("../components/layouts/default/listings/RoomItem.vue")['default']
    'SortBy': typeof import("../components/layouts/default/listings/SortBy.vue")['default']
    'ListingAmenities': typeof import("../components/layouts/default/listings/listing/ListingAmenities.vue")['default']
    'ListingGallery': typeof import("../components/layouts/default/listings/listing/ListingGallery.vue")['default']
    'ListingPricing': typeof import("../components/layouts/default/listings/listing/ListingPricing.vue")['default']
    'ListingTitlebar': typeof import("../components/layouts/default/listings/listing/ListingTitlebar.vue")['default']
    'ViewingScheduleForm': typeof import("../components/layouts/default/listings/listing/ViewingScheduleForm.vue")['default']
    'Titlebar': typeof import("../components/layouts/management/Titlebar.vue")['default']
    'ContractList': typeof import("../components/layouts/management/contract/ContractList.vue")['default']
    'ScheduleBookingFilter': typeof import("../components/layouts/management/schedule_booking/ScheduleBookingFilter.vue")['default']
    'ScheduleBookingList': typeof import("../components/layouts/management/schedule_booking/ScheduleBookingList.vue")['default']
    'AppFooter': typeof import("../components/partials/AppFooter.vue")['default']
    'ChatBox': typeof import("../components/partials/ChatBox.vue")['default']
    'ChatIcon': typeof import("../components/partials/ChatIcon.vue")['default']
    'DashboardNavigation': typeof import("../components/partials/DashboardNavigation.vue")['default']
    'MainNavigation': typeof import("../components/partials/MainNavigation.vue")['default']
    'MobileNavigation': typeof import("../components/partials/MobileNavigation.vue")['default']
    'UserMenu': typeof import("../components/partials/UserMenu.vue")['default']
    'NuxtWelcome': typeof import("../node_modules/nuxt/dist/app/components/welcome.vue")['default']
    'NuxtLayout': typeof import("../node_modules/nuxt/dist/app/components/nuxt-layout")['default']
    'NuxtErrorBoundary': typeof import("../node_modules/nuxt/dist/app/components/nuxt-error-boundary.vue")['default']
    'ClientOnly': typeof import("../node_modules/nuxt/dist/app/components/client-only")['default']
    'DevOnly': typeof import("../node_modules/nuxt/dist/app/components/dev-only")['default']
    'ServerPlaceholder': typeof import("../node_modules/nuxt/dist/app/components/server-placeholder")['default']
    'NuxtLink': typeof import("../node_modules/nuxt/dist/app/components/nuxt-link")['default']
    'NuxtLoadingIndicator': typeof import("../node_modules/nuxt/dist/app/components/nuxt-loading-indicator")['default']
    'NuxtTime': typeof import("../node_modules/nuxt/dist/app/components/nuxt-time.vue")['default']
    'NuxtRouteAnnouncer': typeof import("../node_modules/nuxt/dist/app/components/nuxt-route-announcer")['default']
    'NuxtImg': typeof import("../node_modules/nuxt/dist/app/components/nuxt-stubs")['NuxtImg']
    'NuxtPicture': typeof import("../node_modules/nuxt/dist/app/components/nuxt-stubs")['NuxtPicture']
    'NuxtPage': typeof import("../node_modules/nuxt/dist/pages/runtime/page")['default']
    'NoScript': typeof import("../node_modules/nuxt/dist/head/runtime/components")['NoScript']
    'Link': typeof import("../node_modules/nuxt/dist/head/runtime/components")['Link']
    'Base': typeof import("../node_modules/nuxt/dist/head/runtime/components")['Base']
    'Title': typeof import("../node_modules/nuxt/dist/head/runtime/components")['Title']
    'Meta': typeof import("../node_modules/nuxt/dist/head/runtime/components")['Meta']
    'Style': typeof import("../node_modules/nuxt/dist/head/runtime/components")['Style']
    'Head': typeof import("../node_modules/nuxt/dist/head/runtime/components")['Head']
    'Html': typeof import("../node_modules/nuxt/dist/head/runtime/components")['Html']
    'Body': typeof import("../node_modules/nuxt/dist/head/runtime/components")['Body']
    'NuxtIsland': typeof import("../node_modules/nuxt/dist/app/components/nuxt-island")['default']
    'NuxtRouteAnnouncer': IslandComponent<typeof import("../node_modules/nuxt/dist/app/components/server-placeholder")['default']>
      'LazyLoading': LazyComponent<typeof import("../components/Loading.vue")['default']>
    'LazySignaturePad': LazyComponent<typeof import("../components/SignaturePad.vue")['default']>
    'LazyForgotPasswordForm': LazyComponent<typeof import("../components/layouts/default/auth/ForgotPasswordForm.vue")['default']>
    'LazyLoginForm': LazyComponent<typeof import("../components/layouts/default/auth/LoginForm.vue")['default']>
    'LazyRegisterForm': LazyComponent<typeof import("../components/layouts/default/auth/RegisterForm.vue")['default']>
    'LazySearchBanner': LazyComponent<typeof import("../components/layouts/default/home/SearchBanner.vue")['default']>
    'LazySectionFeaturedDistricts': LazyComponent<typeof import("../components/layouts/default/home/SectionFeaturedDistricts.vue")['default']>
    'LazySectionFeaturedMotels': LazyComponent<typeof import("../components/layouts/default/home/SectionFeaturedMotels.vue")['default']>
    'LazyFilterWidget': LazyComponent<typeof import("../components/layouts/default/listings/FilterWidget.vue")['default']>
    'LazyMotelItem': LazyComponent<typeof import("../components/layouts/default/listings/MotelItem.vue")['default']>
    'LazyPagination': LazyComponent<typeof import("../components/layouts/default/listings/Pagination.vue")['default']>
    'LazyRoomItem': LazyComponent<typeof import("../components/layouts/default/listings/RoomItem.vue")['default']>
    'LazySortBy': LazyComponent<typeof import("../components/layouts/default/listings/SortBy.vue")['default']>
    'LazyListingAmenities': LazyComponent<typeof import("../components/layouts/default/listings/listing/ListingAmenities.vue")['default']>
    'LazyListingGallery': LazyComponent<typeof import("../components/layouts/default/listings/listing/ListingGallery.vue")['default']>
    'LazyListingPricing': LazyComponent<typeof import("../components/layouts/default/listings/listing/ListingPricing.vue")['default']>
    'LazyListingTitlebar': LazyComponent<typeof import("../components/layouts/default/listings/listing/ListingTitlebar.vue")['default']>
    'LazyViewingScheduleForm': LazyComponent<typeof import("../components/layouts/default/listings/listing/ViewingScheduleForm.vue")['default']>
    'LazyTitlebar': LazyComponent<typeof import("../components/layouts/management/Titlebar.vue")['default']>
    'LazyContractList': LazyComponent<typeof import("../components/layouts/management/contract/ContractList.vue")['default']>
    'LazyScheduleBookingFilter': LazyComponent<typeof import("../components/layouts/management/schedule_booking/ScheduleBookingFilter.vue")['default']>
    'LazyScheduleBookingList': LazyComponent<typeof import("../components/layouts/management/schedule_booking/ScheduleBookingList.vue")['default']>
    'LazyAppFooter': LazyComponent<typeof import("../components/partials/AppFooter.vue")['default']>
    'LazyChatBox': LazyComponent<typeof import("../components/partials/ChatBox.vue")['default']>
    'LazyChatIcon': LazyComponent<typeof import("../components/partials/ChatIcon.vue")['default']>
    'LazyDashboardNavigation': LazyComponent<typeof import("../components/partials/DashboardNavigation.vue")['default']>
    'LazyMainNavigation': LazyComponent<typeof import("../components/partials/MainNavigation.vue")['default']>
    'LazyMobileNavigation': LazyComponent<typeof import("../components/partials/MobileNavigation.vue")['default']>
    'LazyUserMenu': LazyComponent<typeof import("../components/partials/UserMenu.vue")['default']>
    'LazyNuxtWelcome': LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/welcome.vue")['default']>
    'LazyNuxtLayout': LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-layout")['default']>
    'LazyNuxtErrorBoundary': LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-error-boundary.vue")['default']>
    'LazyClientOnly': LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/client-only")['default']>
    'LazyDevOnly': LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/dev-only")['default']>
    'LazyServerPlaceholder': LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/server-placeholder")['default']>
    'LazyNuxtLink': LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-link")['default']>
    'LazyNuxtLoadingIndicator': LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-loading-indicator")['default']>
    'LazyNuxtTime': LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-time.vue")['default']>
    'LazyNuxtRouteAnnouncer': LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-route-announcer")['default']>
    'LazyNuxtImg': LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-stubs")['NuxtImg']>
    'LazyNuxtPicture': LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-stubs")['NuxtPicture']>
    'LazyNuxtPage': LazyComponent<typeof import("../node_modules/nuxt/dist/pages/runtime/page")['default']>
    'LazyNoScript': LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['NoScript']>
    'LazyLink': LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['Link']>
    'LazyBase': LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['Base']>
    'LazyTitle': LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['Title']>
    'LazyMeta': LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['Meta']>
    'LazyStyle': LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['Style']>
    'LazyHead': LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['Head']>
    'LazyHtml': LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['Html']>
    'LazyBody': LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['Body']>
    'LazyNuxtIsland': LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-island")['default']>
    'LazyNuxtRouteAnnouncer': LazyComponent<IslandComponent<typeof import("../node_modules/nuxt/dist/app/components/server-placeholder")['default']>>
}

declare module 'vue' {
  export interface GlobalComponents extends _GlobalComponents { }
}

export const Loading: typeof import("../components/Loading.vue")['default']
export const SignaturePad: typeof import("../components/SignaturePad.vue")['default']
export const ForgotPasswordForm: typeof import("../components/layouts/default/auth/ForgotPasswordForm.vue")['default']
export const LoginForm: typeof import("../components/layouts/default/auth/LoginForm.vue")['default']
export const RegisterForm: typeof import("../components/layouts/default/auth/RegisterForm.vue")['default']
export const SearchBanner: typeof import("../components/layouts/default/home/SearchBanner.vue")['default']
export const SectionFeaturedDistricts: typeof import("../components/layouts/default/home/SectionFeaturedDistricts.vue")['default']
export const SectionFeaturedMotels: typeof import("../components/layouts/default/home/SectionFeaturedMotels.vue")['default']
export const FilterWidget: typeof import("../components/layouts/default/listings/FilterWidget.vue")['default']
export const MotelItem: typeof import("../components/layouts/default/listings/MotelItem.vue")['default']
export const Pagination: typeof import("../components/layouts/default/listings/Pagination.vue")['default']
export const RoomItem: typeof import("../components/layouts/default/listings/RoomItem.vue")['default']
export const SortBy: typeof import("../components/layouts/default/listings/SortBy.vue")['default']
export const ListingAmenities: typeof import("../components/layouts/default/listings/listing/ListingAmenities.vue")['default']
export const ListingGallery: typeof import("../components/layouts/default/listings/listing/ListingGallery.vue")['default']
export const ListingPricing: typeof import("../components/layouts/default/listings/listing/ListingPricing.vue")['default']
export const ListingTitlebar: typeof import("../components/layouts/default/listings/listing/ListingTitlebar.vue")['default']
export const ViewingScheduleForm: typeof import("../components/layouts/default/listings/listing/ViewingScheduleForm.vue")['default']
export const Titlebar: typeof import("../components/layouts/management/Titlebar.vue")['default']
export const ContractList: typeof import("../components/layouts/management/contract/ContractList.vue")['default']
export const ScheduleBookingFilter: typeof import("../components/layouts/management/schedule_booking/ScheduleBookingFilter.vue")['default']
export const ScheduleBookingList: typeof import("../components/layouts/management/schedule_booking/ScheduleBookingList.vue")['default']
export const AppFooter: typeof import("../components/partials/AppFooter.vue")['default']
export const ChatBox: typeof import("../components/partials/ChatBox.vue")['default']
export const ChatIcon: typeof import("../components/partials/ChatIcon.vue")['default']
export const DashboardNavigation: typeof import("../components/partials/DashboardNavigation.vue")['default']
export const MainNavigation: typeof import("../components/partials/MainNavigation.vue")['default']
export const MobileNavigation: typeof import("../components/partials/MobileNavigation.vue")['default']
export const UserMenu: typeof import("../components/partials/UserMenu.vue")['default']
export const NuxtWelcome: typeof import("../node_modules/nuxt/dist/app/components/welcome.vue")['default']
export const NuxtLayout: typeof import("../node_modules/nuxt/dist/app/components/nuxt-layout")['default']
export const NuxtErrorBoundary: typeof import("../node_modules/nuxt/dist/app/components/nuxt-error-boundary.vue")['default']
export const ClientOnly: typeof import("../node_modules/nuxt/dist/app/components/client-only")['default']
export const DevOnly: typeof import("../node_modules/nuxt/dist/app/components/dev-only")['default']
export const ServerPlaceholder: typeof import("../node_modules/nuxt/dist/app/components/server-placeholder")['default']
export const NuxtLink: typeof import("../node_modules/nuxt/dist/app/components/nuxt-link")['default']
export const NuxtLoadingIndicator: typeof import("../node_modules/nuxt/dist/app/components/nuxt-loading-indicator")['default']
export const NuxtTime: typeof import("../node_modules/nuxt/dist/app/components/nuxt-time.vue")['default']
export const NuxtRouteAnnouncer: typeof import("../node_modules/nuxt/dist/app/components/nuxt-route-announcer")['default']
export const NuxtImg: typeof import("../node_modules/nuxt/dist/app/components/nuxt-stubs")['NuxtImg']
export const NuxtPicture: typeof import("../node_modules/nuxt/dist/app/components/nuxt-stubs")['NuxtPicture']
export const NuxtPage: typeof import("../node_modules/nuxt/dist/pages/runtime/page")['default']
export const NoScript: typeof import("../node_modules/nuxt/dist/head/runtime/components")['NoScript']
export const Link: typeof import("../node_modules/nuxt/dist/head/runtime/components")['Link']
export const Base: typeof import("../node_modules/nuxt/dist/head/runtime/components")['Base']
export const Title: typeof import("../node_modules/nuxt/dist/head/runtime/components")['Title']
export const Meta: typeof import("../node_modules/nuxt/dist/head/runtime/components")['Meta']
export const Style: typeof import("../node_modules/nuxt/dist/head/runtime/components")['Style']
export const Head: typeof import("../node_modules/nuxt/dist/head/runtime/components")['Head']
export const Html: typeof import("../node_modules/nuxt/dist/head/runtime/components")['Html']
export const Body: typeof import("../node_modules/nuxt/dist/head/runtime/components")['Body']
export const NuxtIsland: typeof import("../node_modules/nuxt/dist/app/components/nuxt-island")['default']
export const NuxtRouteAnnouncer: IslandComponent<typeof import("../node_modules/nuxt/dist/app/components/server-placeholder")['default']>
export const LazyLoading: LazyComponent<typeof import("../components/Loading.vue")['default']>
export const LazySignaturePad: LazyComponent<typeof import("../components/SignaturePad.vue")['default']>
export const LazyForgotPasswordForm: LazyComponent<typeof import("../components/layouts/default/auth/ForgotPasswordForm.vue")['default']>
export const LazyLoginForm: LazyComponent<typeof import("../components/layouts/default/auth/LoginForm.vue")['default']>
export const LazyRegisterForm: LazyComponent<typeof import("../components/layouts/default/auth/RegisterForm.vue")['default']>
export const LazySearchBanner: LazyComponent<typeof import("../components/layouts/default/home/SearchBanner.vue")['default']>
export const LazySectionFeaturedDistricts: LazyComponent<typeof import("../components/layouts/default/home/SectionFeaturedDistricts.vue")['default']>
export const LazySectionFeaturedMotels: LazyComponent<typeof import("../components/layouts/default/home/SectionFeaturedMotels.vue")['default']>
export const LazyFilterWidget: LazyComponent<typeof import("../components/layouts/default/listings/FilterWidget.vue")['default']>
export const LazyMotelItem: LazyComponent<typeof import("../components/layouts/default/listings/MotelItem.vue")['default']>
export const LazyPagination: LazyComponent<typeof import("../components/layouts/default/listings/Pagination.vue")['default']>
export const LazyRoomItem: LazyComponent<typeof import("../components/layouts/default/listings/RoomItem.vue")['default']>
export const LazySortBy: LazyComponent<typeof import("../components/layouts/default/listings/SortBy.vue")['default']>
export const LazyListingAmenities: LazyComponent<typeof import("../components/layouts/default/listings/listing/ListingAmenities.vue")['default']>
export const LazyListingGallery: LazyComponent<typeof import("../components/layouts/default/listings/listing/ListingGallery.vue")['default']>
export const LazyListingPricing: LazyComponent<typeof import("../components/layouts/default/listings/listing/ListingPricing.vue")['default']>
export const LazyListingTitlebar: LazyComponent<typeof import("../components/layouts/default/listings/listing/ListingTitlebar.vue")['default']>
export const LazyViewingScheduleForm: LazyComponent<typeof import("../components/layouts/default/listings/listing/ViewingScheduleForm.vue")['default']>
export const LazyTitlebar: LazyComponent<typeof import("../components/layouts/management/Titlebar.vue")['default']>
export const LazyContractList: LazyComponent<typeof import("../components/layouts/management/contract/ContractList.vue")['default']>
export const LazyScheduleBookingFilter: LazyComponent<typeof import("../components/layouts/management/schedule_booking/ScheduleBookingFilter.vue")['default']>
export const LazyScheduleBookingList: LazyComponent<typeof import("../components/layouts/management/schedule_booking/ScheduleBookingList.vue")['default']>
export const LazyAppFooter: LazyComponent<typeof import("../components/partials/AppFooter.vue")['default']>
export const LazyChatBox: LazyComponent<typeof import("../components/partials/ChatBox.vue")['default']>
export const LazyChatIcon: LazyComponent<typeof import("../components/partials/ChatIcon.vue")['default']>
export const LazyDashboardNavigation: LazyComponent<typeof import("../components/partials/DashboardNavigation.vue")['default']>
export const LazyMainNavigation: LazyComponent<typeof import("../components/partials/MainNavigation.vue")['default']>
export const LazyMobileNavigation: LazyComponent<typeof import("../components/partials/MobileNavigation.vue")['default']>
export const LazyUserMenu: LazyComponent<typeof import("../components/partials/UserMenu.vue")['default']>
export const LazyNuxtWelcome: LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/welcome.vue")['default']>
export const LazyNuxtLayout: LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-layout")['default']>
export const LazyNuxtErrorBoundary: LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-error-boundary.vue")['default']>
export const LazyClientOnly: LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/client-only")['default']>
export const LazyDevOnly: LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/dev-only")['default']>
export const LazyServerPlaceholder: LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/server-placeholder")['default']>
export const LazyNuxtLink: LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-link")['default']>
export const LazyNuxtLoadingIndicator: LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-loading-indicator")['default']>
export const LazyNuxtTime: LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-time.vue")['default']>
export const LazyNuxtRouteAnnouncer: LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-route-announcer")['default']>
export const LazyNuxtImg: LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-stubs")['NuxtImg']>
export const LazyNuxtPicture: LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-stubs")['NuxtPicture']>
export const LazyNuxtPage: LazyComponent<typeof import("../node_modules/nuxt/dist/pages/runtime/page")['default']>
export const LazyNoScript: LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['NoScript']>
export const LazyLink: LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['Link']>
export const LazyBase: LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['Base']>
export const LazyTitle: LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['Title']>
export const LazyMeta: LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['Meta']>
export const LazyStyle: LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['Style']>
export const LazyHead: LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['Head']>
export const LazyHtml: LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['Html']>
export const LazyBody: LazyComponent<typeof import("../node_modules/nuxt/dist/head/runtime/components")['Body']>
export const LazyNuxtIsland: LazyComponent<typeof import("../node_modules/nuxt/dist/app/components/nuxt-island")['default']>
export const LazyNuxtRouteAnnouncer: LazyComponent<IslandComponent<typeof import("../node_modules/nuxt/dist/app/components/server-placeholder")['default']>>

export const componentNames: string[]
