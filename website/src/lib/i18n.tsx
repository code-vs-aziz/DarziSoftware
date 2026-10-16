"use client";

import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from "react";

export type Lang = "en" | "ur";

const dict = {
  en: {
    brand: "Darzi Baba",
    navHome: "Home",
    navShop: "Shop",
    navCategories: "Categories",
    navMeasurements: "My Orders",
    navCart: "Cart",
    navContact: "Contact",
    navTutorials: "How to Measure",
    ctaShop: "Shop collection",
    ctaMeasure: "Track my orders",
    heroTitle: "Crafted for the modern gentleman",
    heroSubtitle:
      "Browse new articles, fabrics, and finishes — or open your orders to see status, history, and balance.",
    newArrivals: "New arrivals",
    featured: "Featured articles",
    byCategory: "Shop by category",
    popular: "Popular picks",
    viewAll: "View all",
    addToCart: "Add to cart",
    viewCart: "View cart",
    buyNow: "Order now",
    priceFrom: "From",
    outOfStock: "Unavailable",
    emptyShop: "New articles are being prepared. Please check back soon.",
    measureTitle: "My orders",
    measureIntro:
      "Enter the phone number registered at our shop. We will send a verification code, then show your suits, payments, and balance.",
    phoneLabel: "Registered phone number",
    phoneHint: "Use the same number on your shop record (e.g. 03XXXXXXXXX).",
    continue: "Continue",
    otpTitle: "Verify it’s you",
    otpHint: "Enter the 6-digit code sent to",
    verify: "Verify",
    back: "Back",
    signOut: "Sign out",
    myProfile: "My profile",
    myMeasurements: "My naap",
    myHistory: "History",
    currentOrders: "Orders",
    viewOnlyNote: "View only. Status, payments, and measurements are updated by the shop.",
    noCurrentOrders: "No suits are in progress right now.",
    seeHistory: "View order history",
    paid: "Paid",
    balance: "Balance",
    outstanding: "Outstanding",
    totalPaid: "Total paid",
    suits: "Suits",
    design: "Design",
    remarks: "Notes",
    paidInFull: "Paid in full",
    discount: "Discount",
    statusBooking: "Booking",
    statusCutting: "Cutting",
    statusAfterCutting: "After cutting",
    statusStitching: "Stitching",
    statusReady: "Ready",
    statusDelivered: "Delivered",
    tutorialsLead: "A guide to the measurements our workshop records for each garment.",
    updateMeasurements: "Request measurement update",
    viewMeasurements: "View measurements",
    measurementDate: "Last updated",
    customerNo: "Customer no.",
    naapNo: "Naap no.",
    phone: "Phone",
    city: "City",
    orders: "Orders",
    noMeasurement: "No measurements on file yet. Please visit the shop to record them.",
    noHistory: "No completed orders yet.",
    submitForReview: "Submit for shop review",
    submitting: "Submitting…",
    submitted: "Your request was sent for shop review.",
    order: "Order",
    orderDate: "Order date",
    deliveryDate: "Ready date",
    articles: "Articles",
    total: "Total",
    cartTitle: "Your cart",
    cartEmpty: "Your cart is empty.",
    checkout: "Place order",
    fullName: "Full name",
    address: "Delivery address",
    paymentCod: "Cash on delivery / pay in shop",
    orderPlaced: "Order placed successfully.",
    remove: "Remove",
    contactTitle: "Contact",
    aboutTitle: "About Darzi Baba",
    aboutBody:
      "Darzi Baba is a trusted tailoring house. Discover curated articles and follow the suits we are making for you.",
    quickLinks: "Quick links",
    support: "Customer support",
    rights: "All rights reserved.",
    poweredBy: "Powered by Azhar Rehman",
    langEn: "English",
    langUr: "اردو",
    loading: "Loading…",
    checking: "Please wait…",
    networkError: "Could not reach the server. Please try again.",
    timeoutError: "Request timed out. Please try again.",
    otpCooldown: "Resend in {s}",
    submitCooldown: "You can submit again in {s}",
    submitValidationError: "Some measurement values need a quick fix. Check highlighted fields.",
    submitEmptyError: "Enter at least one measurement before submitting.",
    submitFailed: "Could not submit. Please try again.",
    notFoundCustomer: "No customer found for this phone number.",
    invalidOtp: "Invalid or expired code. Try again.",
    sku: "SKU",
    description: "Description",
    related: "You may also like",
    menu: "Menu",
    close: "Close",
    featuredBadge: "Featured",
    howItWorksTitle: "Two simple ways to begin",
    howShop: "Browse & order",
    howShopBody: "Explore new articles and place an order for delivery or pickup.",
    howMeasure: "Your orders",
    howMeasureBody: "Verify with your phone to see each suit’s status, your order history, and what is still to pay.",
  },
  ur: {
    brand: "درزی بابا",
    navHome: "ہوم",
    navShop: "شاپ",
    navCategories: "کیٹگریز",
    navMeasurements: "میرے آرڈرز",
    navCart: "کارٹ",
    navContact: "رابطہ",
    navTutorials: "ناپ کیسے لیں",
    ctaShop: "کلیکشن دیکھیں",
    ctaMeasure: "میرے آرڈرز دیکھیں",
    heroTitle: "جدید ذوق کے لیے تیار کردہ",
    heroSubtitle:
      "نئے آرٹیکلز، کپڑے اور فنشز دیکھیں — یا اپنے آرڈرز کھول کر حالت، ہسٹری اور بقایا دیکھیں۔",
    newArrivals: "نئی آمد",
    featured: "نمایاں آرٹیکلز",
    byCategory: "کیٹگری کے مطابق",
    popular: "مقبول انتخاب",
    viewAll: "سب دیکھیں",
    addToCart: "کارٹ میں ڈالیں",
    viewCart: "کارٹ دیکھیں",
    buyNow: "آرڈر کریں",
    priceFrom: "سے",
    outOfStock: "دستیاب نہیں",
    emptyShop: "نئے آرٹیکلز تیار ہو رہے ہیں۔ جلد دوبارہ چیک کریں۔",
    measureTitle: "میرے آرڈرز",
    measureIntro:
      "وہ فون نمبر درج کریں جو دکان پر رجسٹرڈ ہے۔ تصدیقی کوڈ بھیجا جائے گا، پھر آپ کے سوٹ، ادائیگی اور بقایا دکھایا جائے گا۔",
    phoneLabel: "رجسٹرڈ فون نمبر",
    phoneHint: "وہی نمبر استعمال کریں جو شاپ ریکارڈ پر ہے (جیسے 03XXXXXXXXX)۔",
    continue: "آگے بڑھیں",
    otpTitle: "تصدیق کریں",
    otpHint: "بھیجا گیا ٦ ہندسوں کا کوڈ درج کریں",
    verify: "تصدیق",
    back: "واپس",
    signOut: "سائن آؤٹ",
    myProfile: "میرا پروفائل",
    myMeasurements: "میرا ناپ",
    myHistory: "ہسٹری",
    currentOrders: "آرڈرز",
    viewOnlyNote: "صرف دیکھنے کے لیے۔ حالت، ادائیگی اور ناپ دکان اپڈیٹ کرتی ہے۔",
    noCurrentOrders: "اس وقت کوئی سوٹ تیار ہو رہا نہیں۔",
    seeHistory: "آرڈر ہسٹری دیکھیں",
    paid: "ادا شدہ",
    balance: "بقایا",
    outstanding: "کل بقایا",
    totalPaid: "کل ادا شدہ",
    suits: "سوٹ",
    design: "ڈیزائن",
    remarks: "نوٹ",
    paidInFull: "مکمل ادا",
    discount: "رعایت",
    statusBooking: "بکنگ",
    statusCutting: "کٹنگ",
    statusAfterCutting: "کٹنگ کے بعد",
    statusStitching: "سلائی",
    statusReady: "تیار",
    statusDelivered: "ڈیلیورڈ",
    tutorialsLead: "ہر لباس کے لیے ورکشاپ جو ناپ رکھتی ہے، اس کی رہنمائی۔",
    updateMeasurements: "ناپ اپڈیٹ کی درخواست",
    viewMeasurements: "ناپ دیکھیں",
    measurementDate: "آخری اپڈیٹ",
    customerNo: "کسٹمر نمبر",
    naapNo: "ناپ نمبر",
    phone: "فون",
    city: "شہر",
    orders: "آرڈرز",
    noMeasurement: "ابھی کوئی ناپ محفوظ نہیں۔ براہ کرم دکان پر ناپ کروائیں۔",
    noHistory: "ابھی کوئی مکمل آرڈر نہیں۔",
    submitForReview: "دکان کے جائزے کے لیے بھیجیں",
    submitting: "بھیجا جا رہا ہے…",
    submitted: "آپ کی درخواست جائزے کے لیے بھیج دی گئی۔",
    order: "آرڈر",
    orderDate: "آرڈر کی تاریخ",
    deliveryDate: "تیاری کی تاریخ",
    articles: "آرٹیکلز",
    total: "کل",
    cartTitle: "آپ کا کارٹ",
    cartEmpty: "کارٹ خالی ہے۔",
    checkout: "آرڈر کریں",
    fullName: "پورا نام",
    address: "ڈیلیوری ایڈریس",
    paymentCod: "کیش آن ڈیلیوری / دکان پر ادائیگی",
    orderPlaced: "آرڈر کامیابی سے ہو گیا۔",
    remove: "ہٹائیں",
    contactTitle: "رابطہ",
    aboutTitle: "درزی بابا کے بارے میں",
    aboutBody:
      "درزی بابا ایک معتبر درزی گھر ہے۔ منتخب آرٹیکلز دیکھیں اور اپنے سوٹ کی تیاری فالو کریں۔",
    quickLinks: "فوری لنکس",
    support: "کسٹمر سپورٹ",
    rights: "جملہ حقوق محفوظ ہیں۔",
    poweredBy: "Powered by Azhar Rehman",
    langEn: "English",
    langUr: "اردو",
    loading: "لوڈ ہو رہا ہے…",
    checking: "براہ کرم انتظار…",
    networkError: "سرور سے رابطہ نہیں ہو سکا۔ دوبارہ کوشش کریں۔",
    timeoutError: "درخواست کا وقت ختم ہو گیا۔ دوبارہ کوشش کریں۔",
    otpCooldown: "{s} میں دوبارہ بھیج سکتے ہیں",
    submitCooldown: "{s} میں دوبارہ جمع کروا سکتے ہیں",
    submitValidationError: "کچھ ناپ درست نہیں۔ فیلڈز چیک کریں۔",
    submitEmptyError: "جمع کرانے سے پہلے کم از کم ایک ناپ لکھیں۔",
    submitFailed: "جمع نہیں ہو سکا۔ دوبارہ کوشش کریں۔",
    notFoundCustomer: "اس فون نمبر سے کوئی کسٹمر نہیں ملا۔",
    invalidOtp: "غلط یا ختم شدہ کوڈ۔ دوبارہ کوشش کریں۔",
    sku: "SKU",
    description: "تفصیل",
    related: "آپ کو یہ بھی پسند آ سکتا ہے",
    menu: "مینو",
    close: "بند کریں",
    featuredBadge: "نمایاں",
    howItWorksTitle: "شروع کرنے کے دو آسان طریقے",
    howShop: "دیکھیں اور آرڈر کریں",
    howShopBody: "نئے آرٹیکلز دیکھیں اور ڈیلیوری یا پک اپ کے لیے آرڈر دیں۔",
    howMeasure: "آپ کے آرڈرز",
    howMeasureBody: "فون سے تصدیق کریں اور ہر سوٹ کی حالت، پرانی ہسٹری اور بقایا رقم دیکھیں۔",
  },
} as const;

export type DictKey = keyof typeof dict.en;

type I18nCtx = {
  lang: Lang;
  setLang: (l: Lang) => void;
  t: (key: DictKey) => string;
  dir: "ltr" | "rtl";
};

const Ctx = createContext<I18nCtx | null>(null);
const LANG_KEY = "db_lang";

export function LanguageProvider({ children }: { children: ReactNode }) {
  const [lang, setLangState] = useState<Lang>("en");

  useEffect(() => {
    const saved = (localStorage.getItem(LANG_KEY) as Lang | null) || "en";
    if (saved === "en" || saved === "ur") setLangState(saved);
  }, []);

  const setLang = useCallback((l: Lang) => {
    setLangState(l);
    localStorage.setItem(LANG_KEY, l);
    document.documentElement.lang = l === "ur" ? "ur" : "en";
    document.documentElement.dir = l === "ur" ? "rtl" : "ltr";
  }, []);

  useEffect(() => {
    document.documentElement.lang = lang === "ur" ? "ur" : "en";
    document.documentElement.dir = lang === "ur" ? "rtl" : "ltr";
  }, [lang]);

  const value = useMemo<I18nCtx>(
    () => ({
      lang,
      setLang,
      dir: lang === "ur" ? "rtl" : "ltr",
      t: (key) => dict[lang][key] || dict.en[key],
    }),
    [lang, setLang]
  );

  return <Ctx.Provider value={value}>{children}</Ctx.Provider>;
}

export function useI18n() {
  const ctx = useContext(Ctx);
  if (!ctx) throw new Error("useI18n requires LanguageProvider");
  return ctx;
}

export function pickLocale<T extends { [k: string]: unknown }>(
  lang: Lang,
  enKey: keyof T,
  urKey: keyof T,
  obj: T
): string {
  const ur = obj[urKey];
  const en = obj[enKey];
  if (lang === "ur" && typeof ur === "string" && ur.trim()) return ur;
  if (typeof en === "string") return en;
  return "";
}
