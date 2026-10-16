const API_BASE =
  (typeof process !== "undefined" && process.env.NEXT_PUBLIC_API_BASE_URL
    ? process.env.NEXT_PUBLIC_API_BASE_URL.replace(/\/$/, "")
    : "") || "";

export type ApiResult<T> =
  | ({ ok: true } & T)
  | { ok: false; error: string; errors?: Record<string, string>; message?: string; retry_after?: number };

async function request<T>(
  path: string,
  options: RequestInit & { token?: string; timeoutMs?: number } = {}
): Promise<ApiResult<T>> {
  const { token, headers, timeoutMs = 20000, ...rest } = options;
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), timeoutMs);

  try {
    const res = await fetch(`${API_BASE}${path.startsWith("/") ? path : `/${path}`}`, {
      ...rest,
      signal: controller.signal,
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        ...headers,
      },
      cache: "no-store",
    });

    let data: ApiResult<T>;
    try {
      data = (await res.json()) as ApiResult<T>;
    } catch {
      return { ok: false, error: "invalid_json" };
    }
    return data;
  } catch (err) {
    const aborted = err instanceof DOMException && err.name === "AbortError";
    return { ok: false, error: aborted ? "timeout" : "network_error" };
  } finally {
    clearTimeout(timer);
  }
}

export const api = {
  health: () => request<{ service: string; schema: boolean }>("/api/content/health"),
  home: () =>
    request<{
      settings: HomeSettings;
      sections: HomeSection[];
      faqs: Faq[];
    }>("/api/content/home"),
  lookup: (identifier: string) =>
    request<{
      challenge_id: number;
      phone_masked: string;
      customer_name: string;
      man_napno?: string;
      delivery?: string;
      debug_otp?: string;
    }>("/api/auth/lookup", {
      method: "POST",
      body: JSON.stringify({ identifier }),
    }),
  verify: (challenge_id: number, otp: string) =>
    request<{
      token: string;
      expires_in: number;
      customer: PublicCustomer;
    }>("/api/auth/verify", {
      method: "POST",
      body: JSON.stringify({ challenge_id, otp }),
    }),
  me: (token: string) =>
    request<{ customer: PublicCustomer }>("/api/auth/me", { token }),
  logout: (token: string) =>
    request<Record<string, never>>("/api/auth/logout", { method: "POST", token }),
  measurementCatalog: () =>
    request<{ catalog: MeasurementGroup[] }>("/api/measurements/catalog"),
  myMeasurement: (token: string) =>
    request<{
      customer: PublicCustomer;
      measurement: MeasurementPayload | null;
      submissions: SubmissionSummary[];
      tailoring_history: TailoringOrder[];
      stats: PortalStats;
    }>("/api/measurements/me", { token }),
  products: (query = "") =>
    request<{ products: ProductCard[] }>(`/api/shop/products${query}`),
  categories: () =>
    request<{ categories: Category[] }>("/api/shop/categories"),
  product: (slug: string) =>
    request<{ product: ProductDetail }>(`/api/shop/product/${encodeURIComponent(slug)}`),
  checkout: (body: CheckoutBody, token?: string) =>
    request<{
      order_id: number;
      order_number: string;
      total: number;
      status: string;
      payment_method: string;
    }>("/api/shop/checkout", {
      method: "POST",
      token,
      body: JSON.stringify(body),
    }),
};

export type HomeSettings = {
  site_name: string;
  tagline_en: string;
  tagline_ur: string;
  about_en: string;
  about_ur: string;
  contact_phone: string;
  contact_whatsapp: string;
  contact_email: string;
  contact_address_en: string;
  contact_address_ur: string;
  seo_title_default: string;
  seo_description_default: string;
  og_image_url: string;
  feature_shop: number;
  feature_measurements: number;
  shop_name: string;
};

export type HomeSection = {
  section_key: string;
  title_en: string;
  title_ur: string;
  body_en: string;
  body_ur: string;
  cta_label_en: string;
  cta_label_ur: string;
  cta_url: string;
  image_url: string;
};

export type Faq = {
  question_en: string;
  question_ur: string;
  answer_en: string;
  answer_ur: string;
};

export type PublicCustomer = {
  code: number;
  name: string;
  man_napno: string;
  phone_masked: string;
  phone?: string;
  city: string;
  address?: string;
};

export type SuitStatus =
  | "booking"
  | "cutting"
  | "after_cutting"
  | "stitching"
  | "ready"
  | "delivered";

export type TailoringSuit = {
  name: string;
  type: string;
  design: string;
  rate: number;
  remarks: string;
  status: SuitStatus;
};

export type TailoringOrder = {
  order_no: number;
  order_date: string;
  delivery_date: string;
  qty: number;
  total: number;
  discount: number;
  advance: number;
  paid: number;
  balance: number;
  status: SuitStatus;
  is_current: boolean;
  image_url: string;
  articles: TailoringSuit[];
};

export type PortalStats = {
  order_count: number;
  current_count: number;
  history_count: number;
  total_paid: number;
  outstanding: number;
  pending_balance: number;
};

export type Category = {
  id: number;
  slug: string;
  name_en: string;
  name_ur: string;
};

export type MeasurementField = {
  key: string;
  label_en: string;
  label_ur: string;
  hint_en?: string;
  hint_ur?: string;
  video_url?: string;
  example_value?: string;
  unit?: string;
  min?: number;
  max?: number;
};

export type MeasurementGroup = {
  garment: string;
  label_en: string;
  label_ur: string;
  fields: MeasurementField[];
};

export type MeasurementPayload = {
  code: number;
  customer_code: number;
  date: string;
  remarks: string;
  values: Record<string, string>;
  source: string;
};

export type SubmissionSummary = {
  id: number;
  status: string;
  submission_type: string;
  created_at: string;
  reviewed_at: string | null;
  review_note: string | null;
};

export type ProductCard = {
  id: number;
  slug: string;
  name_en: string;
  name_ur: string;
  short_desc_en: string;
  short_desc_ur: string;
  price: number;
  image: string;
  is_featured: number;
};

export type ProductDetail = ProductCard & {
  description_en: string;
  description_ur: string;
  images: { image_url: string; alt_en: string }[];
  sku: string;
};

export type CheckoutBody = {
  items: { product_id: number; qty: number }[];
  name?: string;
  phone?: string;
  address?: string;
  note?: string;
  payment_method?: "cod" | "pay_in_shop";
};

export const TOKEN_KEY = "db_customer_token";
