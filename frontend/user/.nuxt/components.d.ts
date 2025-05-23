
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
      'AuthForm': typeof import("../components/AuthForm.vue")['default']
    'Loading': typeof import("../components/Loading.vue")['default']
    'ContactDetails': typeof import("../components/contact/ContactDetails.vue")['default']
    'ContactForm': typeof import("../components/contact/ContactForm.vue")['default']
    'ContactMap': typeof import("../components/contact/ContactMap.vue")['default']
    'SearchBanner': typeof import("../components/home/SearchBanner.vue")['default']
    'SectionFeaturedAreas': typeof import("../components/home/SectionFeaturedAreas.vue")['default']
    'SectionFeaturedRentals': typeof import("../components/home/SectionFeaturedRentals.vue")['default']
    'SectionRentalSteps': typeof import("../components/home/SectionRentalSteps.vue")['default']
    'AppFooter': typeof import("../components/layout/AppFooter.vue")['default']
    'DashboardNav': typeof import("../components/layout/DashboardNav.vue")['default']
    'HeaderRightSide': typeof import("../components/layout/HeaderRightSide.vue")['default']
    'FilterWidget': typeof import("../components/listings/FilterWidget.vue")['default']
    'ListingItem': typeof import("../components/listings/ListingItem.vue")['default']
    'ListingItemChild': typeof import("../components/listings/ListingItemChild.vue")['default']
    'Pagination': typeof import("../components/listings/Pagination.vue")['default']
    'SortBy': typeof import("../components/listings/SortBy.vue")['default']
    'Titlebar': typeof import("../components/listings/Titlebar.vue")['default']
    'BookingWidget': typeof import("../components/listings/listing/BookingWidget.vue")['default']
    'ListingAmenities': typeof import("../components/listings/listing/ListingAmenities.vue")['default']
    'ListingMap': typeof import("../components/listings/listing/ListingMap.vue")['default']
    'ListingPricing': typeof import("../components/listings/listing/ListingPricing.vue")['default']
    'ListingShare': typeof import("../components/listings/listing/ListingShare.vue")['default']
    'ListingSlider': typeof import("../components/listings/listing/ListingSlider.vue")['default']
    'ListingSliderSmall': typeof import("../components/listings/listing/ListingSliderSmall.vue")['default']
    'ListingTitlebar': typeof import("../components/listings/listing/ListingTitlebar.vue")['default']
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
      'LazyAuthForm': LazyComponent<typeof import("../components/AuthForm.vue")['default']>
    'LazyLoading': LazyComponent<typeof import("../components/Loading.vue")['default']>
    'LazyContactDetails': LazyComponent<typeof import("../components/contact/ContactDetails.vue")['default']>
    'LazyContactForm': LazyComponent<typeof import("../components/contact/ContactForm.vue")['default']>
    'LazyContactMap': LazyComponent<typeof import("../components/contact/ContactMap.vue")['default']>
    'LazySearchBanner': LazyComponent<typeof import("../components/home/SearchBanner.vue")['default']>
    'LazySectionFeaturedAreas': LazyComponent<typeof import("../components/home/SectionFeaturedAreas.vue")['default']>
    'LazySectionFeaturedRentals': LazyComponent<typeof import("../components/home/SectionFeaturedRentals.vue")['default']>
    'LazySectionRentalSteps': LazyComponent<typeof import("../components/home/SectionRentalSteps.vue")['default']>
    'LazyAppFooter': LazyComponent<typeof import("../components/layout/AppFooter.vue")['default']>
    'LazyDashboardNav': LazyComponent<typeof import("../components/layout/DashboardNav.vue")['default']>
    'LazyHeaderRightSide': LazyComponent<typeof import("../components/layout/HeaderRightSide.vue")['default']>
    'LazyFilterWidget': LazyComponent<typeof import("../components/listings/FilterWidget.vue")['default']>
    'LazyListingItem': LazyComponent<typeof import("../components/listings/ListingItem.vue")['default']>
    'LazyListingItemChild': LazyComponent<typeof import("../components/listings/ListingItemChild.vue")['default']>
    'LazyPagination': LazyComponent<typeof import("../components/listings/Pagination.vue")['default']>
    'LazySortBy': LazyComponent<typeof import("../components/listings/SortBy.vue")['default']>
    'LazyTitlebar': LazyComponent<typeof import("../components/listings/Titlebar.vue")['default']>
    'LazyBookingWidget': LazyComponent<typeof import("../components/listings/listing/BookingWidget.vue")['default']>
    'LazyListingAmenities': LazyComponent<typeof import("../components/listings/listing/ListingAmenities.vue")['default']>
    'LazyListingMap': LazyComponent<typeof import("../components/listings/listing/ListingMap.vue")['default']>
    'LazyListingPricing': LazyComponent<typeof import("../components/listings/listing/ListingPricing.vue")['default']>
    'LazyListingShare': LazyComponent<typeof import("../components/listings/listing/ListingShare.vue")['default']>
    'LazyListingSlider': LazyComponent<typeof import("../components/listings/listing/ListingSlider.vue")['default']>
    'LazyListingSliderSmall': LazyComponent<typeof import("../components/listings/listing/ListingSliderSmall.vue")['default']>
    'LazyListingTitlebar': LazyComponent<typeof import("../components/listings/listing/ListingTitlebar.vue")['default']>
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

export const AuthForm: typeof import("../components/AuthForm.vue")['default']
export const Loading: typeof import("../components/Loading.vue")['default']
export const ContactDetails: typeof import("../components/contact/ContactDetails.vue")['default']
export const ContactForm: typeof import("../components/contact/ContactForm.vue")['default']
export const ContactMap: typeof import("../components/contact/ContactMap.vue")['default']
export const SearchBanner: typeof import("../components/home/SearchBanner.vue")['default']
export const SectionFeaturedAreas: typeof import("../components/home/SectionFeaturedAreas.vue")['default']
export const SectionFeaturedRentals: typeof import("../components/home/SectionFeaturedRentals.vue")['default']
export const SectionRentalSteps: typeof import("../components/home/SectionRentalSteps.vue")['default']
export const AppFooter: typeof import("../components/layout/AppFooter.vue")['default']
export const DashboardNav: typeof import("../components/layout/DashboardNav.vue")['default']
export const HeaderRightSide: typeof import("../components/layout/HeaderRightSide.vue")['default']
export const FilterWidget: typeof import("../components/listings/FilterWidget.vue")['default']
export const ListingItem: typeof import("../components/listings/ListingItem.vue")['default']
export const ListingItemChild: typeof import("../components/listings/ListingItemChild.vue")['default']
export const Pagination: typeof import("../components/listings/Pagination.vue")['default']
export const SortBy: typeof import("../components/listings/SortBy.vue")['default']
export const Titlebar: typeof import("../components/listings/Titlebar.vue")['default']
export const BookingWidget: typeof import("../components/listings/listing/BookingWidget.vue")['default']
export const ListingAmenities: typeof import("../components/listings/listing/ListingAmenities.vue")['default']
export const ListingMap: typeof import("../components/listings/listing/ListingMap.vue")['default']
export const ListingPricing: typeof import("../components/listings/listing/ListingPricing.vue")['default']
export const ListingShare: typeof import("../components/listings/listing/ListingShare.vue")['default']
export const ListingSlider: typeof import("../components/listings/listing/ListingSlider.vue")['default']
export const ListingSliderSmall: typeof import("../components/listings/listing/ListingSliderSmall.vue")['default']
export const ListingTitlebar: typeof import("../components/listings/listing/ListingTitlebar.vue")['default']
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
export const LazyAuthForm: LazyComponent<typeof import("../components/AuthForm.vue")['default']>
export const LazyLoading: LazyComponent<typeof import("../components/Loading.vue")['default']>
export const LazyContactDetails: LazyComponent<typeof import("../components/contact/ContactDetails.vue")['default']>
export const LazyContactForm: LazyComponent<typeof import("../components/contact/ContactForm.vue")['default']>
export const LazyContactMap: LazyComponent<typeof import("../components/contact/ContactMap.vue")['default']>
export const LazySearchBanner: LazyComponent<typeof import("../components/home/SearchBanner.vue")['default']>
export const LazySectionFeaturedAreas: LazyComponent<typeof import("../components/home/SectionFeaturedAreas.vue")['default']>
export const LazySectionFeaturedRentals: LazyComponent<typeof import("../components/home/SectionFeaturedRentals.vue")['default']>
export const LazySectionRentalSteps: LazyComponent<typeof import("../components/home/SectionRentalSteps.vue")['default']>
export const LazyAppFooter: LazyComponent<typeof import("../components/layout/AppFooter.vue")['default']>
export const LazyDashboardNav: LazyComponent<typeof import("../components/layout/DashboardNav.vue")['default']>
export const LazyHeaderRightSide: LazyComponent<typeof import("../components/layout/HeaderRightSide.vue")['default']>
export const LazyFilterWidget: LazyComponent<typeof import("../components/listings/FilterWidget.vue")['default']>
export const LazyListingItem: LazyComponent<typeof import("../components/listings/ListingItem.vue")['default']>
export const LazyListingItemChild: LazyComponent<typeof import("../components/listings/ListingItemChild.vue")['default']>
export const LazyPagination: LazyComponent<typeof import("../components/listings/Pagination.vue")['default']>
export const LazySortBy: LazyComponent<typeof import("../components/listings/SortBy.vue")['default']>
export const LazyTitlebar: LazyComponent<typeof import("../components/listings/Titlebar.vue")['default']>
export const LazyBookingWidget: LazyComponent<typeof import("../components/listings/listing/BookingWidget.vue")['default']>
export const LazyListingAmenities: LazyComponent<typeof import("../components/listings/listing/ListingAmenities.vue")['default']>
export const LazyListingMap: LazyComponent<typeof import("../components/listings/listing/ListingMap.vue")['default']>
export const LazyListingPricing: LazyComponent<typeof import("../components/listings/listing/ListingPricing.vue")['default']>
export const LazyListingShare: LazyComponent<typeof import("../components/listings/listing/ListingShare.vue")['default']>
export const LazyListingSlider: LazyComponent<typeof import("../components/listings/listing/ListingSlider.vue")['default']>
export const LazyListingSliderSmall: LazyComponent<typeof import("../components/listings/listing/ListingSliderSmall.vue")['default']>
export const LazyListingTitlebar: LazyComponent<typeof import("../components/listings/listing/ListingTitlebar.vue")['default']>
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
