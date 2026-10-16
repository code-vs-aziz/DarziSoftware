"use client";

import { FormEvent, useEffect, useMemo, useState } from "react";
import {
  api,
  MeasurementGroup,
  MeasurementPayload,
  PortalStats,
  PublicCustomer,
  SuitStatus,
  TailoringOrder,
  TOKEN_KEY,
} from "@/lib/api";
import { DictKey, useI18n } from "@/lib/i18n";

type Step = "lookup" | "otp" | "portal";
type PortalTab = "current" | "history" | "measurements";

const EMPTY_STATS: PortalStats = {
  order_count: 0,
  current_count: 0,
  history_count: 0,
  total_paid: 0,
  outstanding: 0,
  pending_balance: 0,
};

const STATUS_STEPS: SuitStatus[] = [
  "booking",
  "cutting",
  "after_cutting",
  "stitching",
  "ready",
  "delivered",
];

const STATUS_LABEL: Record<SuitStatus, DictKey> = {
  booking: "statusBooking",
  cutting: "statusCutting",
  after_cutting: "statusAfterCutting",
  stitching: "statusStitching",
  ready: "statusReady",
  delivered: "statusDelivered",
};

function formatMoney(value: number) {
  const amount = Math.round((Number(value) || 0) * 100) / 100;
  const text = Number.isInteger(amount)
    ? amount.toLocaleString("en-PK")
    : amount.toLocaleString("en-PK", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  return `PKR ${text}`;
}

function formatDate(raw: string, lang: "en" | "ur") {
  if (!raw || raw.startsWith("0000")) return "";
  const parsed = new Date(raw.includes("T") ? raw : raw.replace(" ", "T"));
  if (Number.isNaN(parsed.getTime())) return raw;
  return parsed.toLocaleDateString(lang === "ur" ? "ur-PK" : "en-GB", {
    day: "numeric",
    month: "short",
    year: "numeric",
  });
}

function usableDesign(value: string | number | null | undefined) {
  const text = String(value ?? "").trim();
  if (!text || text === "0" || text === "0.0") return "";
  return text;
}

export default function MeasurementsPage() {
  const { t, lang } = useI18n();
  const [step, setStep] = useState<Step>("lookup");
  const [phone, setPhone] = useState("");
  const [challengeId, setChallengeId] = useState(0);
  const [phoneMasked, setPhoneMasked] = useState("");
  const [otp, setOtp] = useState("");
  const [debugOtp, setDebugOtp] = useState("");
  const [token, setToken] = useState("");
  const [customer, setCustomer] = useState<PublicCustomer | null>(null);
  const [measurement, setMeasurement] = useState<MeasurementPayload | null>(null);
  const [catalog, setCatalog] = useState<MeasurementGroup[]>([]);
  const [activeGarment, setActiveGarment] = useState("kurta");
  const [history, setHistory] = useState<TailoringOrder[]>([]);
  const [stats, setStats] = useState<PortalStats>(EMPTY_STATS);
  const [tab, setTab] = useState<PortalTab>("current");
  const [error, setError] = useState("");
  const [info, setInfo] = useState("");
  const [busy, setBusy] = useState(false);
  const [cooldownSec, setCooldownSec] = useState(0);

  useEffect(() => {
    const saved = localStorage.getItem(TOKEN_KEY) || "";
    if (!saved) return;
    setToken(saved);
    void bootstrapSession(saved);
  }, []);

  useEffect(() => {
    if (cooldownSec <= 0) return;
    const id = window.setInterval(() => {
      setCooldownSec((s) => (s <= 1 ? 0 : s - 1));
    }, 1000);
    return () => window.clearInterval(id);
    // Only (re)start the ticker when cooldown becomes active.
    // eslint-disable-next-line react-hooks/exhaustive-deps -- intentional: cooldownSec > 0
  }, [cooldownSec > 0]);

  function formatCooldown(sec: number) {
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    return `${m}:${String(s).padStart(2, "0")}`;
  }

  async function bootstrapSession(sessionToken: string) {
    setBusy(true);
    setError("");
    try {
      const [me, cat] = await Promise.all([
        api.myMeasurement(sessionToken),
        api.measurementCatalog(),
      ]);
      if (!me.ok) {
        localStorage.removeItem(TOKEN_KEY);
        setToken("");
        return;
      }
      const orders = me.tailoring_history || [];
      setCustomer(me.customer);
      setMeasurement(me.measurement);
      setHistory(orders);
      setStats(me.stats || EMPTY_STATS);
      if (cat.ok) {
        setCatalog(cat.catalog);
        const firstWithValues = cat.catalog.find((group) =>
          group.fields.some((field) => Boolean(me.measurement?.values?.[field.key]))
        );
        if (firstWithValues) setActiveGarment(firstWithValues.garment);
        else if (cat.catalog[0]) setActiveGarment(cat.catalog[0].garment);
      }
      setStep("portal");
      setTab(orders.some((order) => order.is_current) ? "current" : "history");
    } finally {
      setBusy(false);
    }
  }

  async function onLookup(e: FormEvent) {
    e.preventDefault();
    if (cooldownSec > 0 || busy) return;
    setBusy(true);
    setError("");
    setInfo("");
    try {
      const res = await api.lookup(phone.trim());
      if (!res.ok) {
        if (res.error === "otp_cooldown" || res.error === "rate_limited") {
          const wait = Math.max(1, Number(res.retry_after) || 60);
          setCooldownSec(wait);
          setError("");
          return;
        }
        setError(
          res.error === "not_found"
            ? t("notFoundCustomer")
            : res.error === "timeout"
              ? t("timeoutError")
              : t("networkError")
        );
        return;
      }
      setCooldownSec(60);
      setChallengeId(res.challenge_id);
      setPhoneMasked(res.phone_masked);
      setDebugOtp(res.debug_otp || "");
      setInfo(
        lang === "ur"
          ? `کوڈ ${res.phone_masked} پر بھیج دیا گیا۔`
          : `Code sent to ${res.phone_masked}.`
      );
      setStep("otp");
    } finally {
      setBusy(false);
    }
  }

  async function onVerify(e: FormEvent) {
    e.preventDefault();
    setBusy(true);
    setError("");
    try {
      const res = await api.verify(challengeId, otp.trim());
      if (!res.ok) {
        setError(
          res.error === "timeout"
            ? t("timeoutError")
            : res.error === "network_error"
              ? t("networkError")
              : t("invalidOtp")
        );
        return;
      }
      localStorage.setItem(TOKEN_KEY, res.token);
      setToken(res.token);
      setCooldownSec(0);
      await bootstrapSession(res.token);
    } finally {
      setBusy(false);
    }
  }

  async function onLogout() {
    if (token) await api.logout(token);
    localStorage.removeItem(TOKEN_KEY);
    setToken("");
    setCustomer(null);
    setMeasurement(null);
    setHistory([]);
    setStats(EMPTY_STATS);
    setStep("lookup");
  }

  const visibleOrders = useMemo(
    () => history.filter((order) => (tab === "current" ? order.is_current : !order.is_current)),
    [history, tab]
  );

  const activeGroup = useMemo(
    () => catalog.find((group) => group.garment === activeGarment) || catalog[0],
    [catalog, activeGarment]
  );

  const activeFields = useMemo(() => {
    if (!activeGroup || !measurement) return [];
    return activeGroup.fields.filter((field) => {
      const value = measurement.values[field.key];
      return value !== undefined && value !== null && String(value).trim() !== "";
    });
  }, [activeGroup, measurement]);

  const initials = (customer?.name || "DB")
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0] || "")
    .join("")
    .toUpperCase();

  const outstanding = Number(stats.outstanding) || 0;
  const measurementDate = formatDate(measurement?.date || "", lang);

  return (
    <section className="db-section">
      <div className="db-wrap db-portal">
        {step !== "portal" ? (
          <>
            <h1 className="db-page-title">{t("measureTitle")}</h1>
            <p className="db-lead">{t("measureIntro")}</p>
          </>
        ) : null}

        {error ? <div className="db-alert db-alert--error">{error}</div> : null}
        {info ? <div className="db-alert db-alert--ok">{info}</div> : null}
        {cooldownSec > 0 && step !== "portal" ? (
          <div className="db-alert db-alert--info" role="status" aria-live="polite">
            {t("otpCooldown").replace("{s}", formatCooldown(cooldownSec))}
          </div>
        ) : null}

        {step === "lookup" ? (
          <form className="db-panel" onSubmit={onLookup}>
            <div className="db-field">
              <label htmlFor="phone">{t("phoneLabel")}</label>
              <input
                id="phone"
                value={phone}
                onChange={(e) => setPhone(e.target.value)}
                required
                autoComplete="tel"
                inputMode="tel"
                placeholder="03XXXXXXXXX"
                disabled={busy}
              />
              <span className="db-hint">{t("phoneHint")}</span>
            </div>
            <button
              className="db-btn db-btn--primary"
              disabled={busy || cooldownSec > 0}
              type="submit"
            >
              {busy
                ? t("checking")
                : cooldownSec > 0
                  ? formatCooldown(cooldownSec)
                  : t("continue")}
            </button>
          </form>
        ) : null}

        {step === "otp" ? (
          <form className="db-panel" onSubmit={onVerify}>
            <h2 style={{ marginTop: 0, fontFamily: "var(--font-display)", fontSize: "1.25rem" }}>
              {t("otpTitle")}
            </h2>
            <p className="db-muted">
              {t("otpHint")} {phoneMasked}
            </p>
            {debugOtp ? <div className="db-alert db-alert--info">OTP: {debugOtp}</div> : null}
            <div className="db-field">
              <label htmlFor="otp">OTP</label>
              <input
                id="otp"
                inputMode="numeric"
                pattern="[0-9]{6}"
                maxLength={6}
                value={otp}
                onChange={(e) => setOtp(e.target.value)}
                required
              />
            </div>
            <div style={{ display: "flex", gap: "0.75rem", flexWrap: "wrap" }}>
              <button className="db-btn db-btn--primary" disabled={busy} type="submit">
                {busy ? t("checking") : t("verify")}
              </button>
              <button className="db-btn db-btn--secondary" type="button" onClick={() => setStep("lookup")}>
                {t("back")}
              </button>
            </div>
          </form>
        ) : null}

        {step === "portal" && customer ? (
          <>
            <div className="db-panel db-portal-hero">
              <div className="db-profile-hero">
                <div className="db-avatar" aria-hidden>
                  {initials}
                </div>
                <div className="db-meta">
                  <h1>{customer.name}</h1>
                  <p className="db-view-note">{t("viewOnlyNote")}</p>
                  <div className="db-chips">
                    {customer.man_napno ? (
                      <button
                        type="button"
                        className="db-chip db-chip--action"
                        onClick={() => setTab("measurements")}
                      >
                        {t("naapNo")}: <bdi>{customer.man_napno}</bdi>
                        <span className="db-chip__hint">{t("viewMeasurements")}</span>
                      </button>
                    ) : null}
                    <span className="db-chip">
                      {t("customerNo")}: <bdi>{customer.code}</bdi>
                    </span>
                    <span className="db-chip">
                      {t("phone")}: <bdi>{customer.phone || customer.phone_masked}</bdi>
                    </span>
                    {customer.city ? (
                      <span className="db-chip">
                        {t("city")}: {customer.city}
                      </span>
                    ) : null}
                  </div>
                </div>
                <button type="button" className="db-btn db-btn--secondary" onClick={onLogout}>
                  {t("signOut")}
                </button>
              </div>
            </div>

            <div className="db-stat-grid">
              <article className="db-stat">
                <span>{t("currentOrders")}</span>
                <strong>{stats.current_count}</strong>
              </article>
              <article className="db-stat">
                <span>{t("totalPaid")}</span>
                <strong>{formatMoney(stats.total_paid)}</strong>
              </article>
              <article className={`db-stat${outstanding > 0 ? " db-stat--due" : " db-stat--clear"}`}>
                <span>{t("outstanding")}</span>
                <strong>{formatMoney(outstanding)}</strong>
              </article>
            </div>

            <div className="db-tabs" role="tablist">
              <button
                type="button"
                role="tab"
                aria-selected={tab === "current"}
                className={`db-tab${tab === "current" ? " is-active" : ""}`}
                onClick={() => setTab("current")}
              >
                {t("currentOrders")}
                <span className="db-tab__count">{stats.current_count}</span>
              </button>
              <button
                type="button"
                role="tab"
                aria-selected={tab === "history"}
                className={`db-tab${tab === "history" ? " is-active" : ""}`}
                onClick={() => setTab("history")}
              >
                {t("myHistory")}
                <span className="db-tab__count">{stats.history_count}</span>
              </button>
              <button
                type="button"
                role="tab"
                aria-selected={tab === "measurements"}
                className={`db-tab${tab === "measurements" ? " is-active" : ""}`}
                onClick={() => setTab("measurements")}
              >
                {t("myMeasurements")}
              </button>
            </div>

            {tab === "measurements" ? (
              <div className="db-panel">
                {!measurement || !Object.values(measurement.values || {}).some((v) => String(v || "").trim()) ? (
                  <div className="db-empty">{t("noMeasurement")}</div>
                ) : (
                  <>
                    {measurementDate ? (
                      <p className="db-muted" style={{ marginTop: 0 }}>
                        {t("measurementDate")}: {measurementDate}
                      </p>
                    ) : null}
                    <div className="db-tabs" style={{ marginTop: 0 }}>
                      {catalog.map((group) => (
                        <button
                          key={group.garment}
                          type="button"
                          className={`db-tab${activeGarment === group.garment ? " is-active" : ""}`}
                          onClick={() => setActiveGarment(group.garment)}
                        >
                          {lang === "ur" ? group.label_ur : group.label_en}
                        </button>
                      ))}
                    </div>
                    {activeFields.length ? (
                      <div className="db-measure-grid">
                        {activeFields.map((field) => (
                          <div className="db-measure-item" key={field.key}>
                            <small>{lang === "ur" ? field.label_ur : field.label_en}</small>
                            <strong>
                              <bdi>{measurement.values[field.key]}</bdi>
                            </strong>
                          </div>
                        ))}
                      </div>
                    ) : (
                      <div className="db-empty">{t("noMeasurement")}</div>
                    )}
                  </>
                )}
              </div>
            ) : null}

            {tab === "current" || tab === "history" ? (
              !visibleOrders.length ? (
                <div className="db-empty">
                  <p>{tab === "current" ? t("noCurrentOrders") : t("noHistory")}</p>
                  {tab === "current" && stats.history_count > 0 ? (
                    <button type="button" className="db-btn db-btn--secondary" onClick={() => setTab("history")}>
                      {t("seeHistory")}
                    </button>
                  ) : null}
                </div>
              ) : (
                <div className="db-order-list">
                  {visibleOrders.map((order) => (
                    <OrderCard key={order.order_no} order={order} lang={lang} />
                  ))}
                </div>
              )
            ) : null}
          </>
        ) : null}
      </div>
    </section>
  );
}

function OrderCard({ order, lang }: { order: TailoringOrder; lang: "en" | "ur" }) {
  const { t } = useI18n();
  const currentIndex = STATUS_STEPS.indexOf(order.status);
  const readyDate = formatDate(order.delivery_date, lang);
  const orderDate = formatDate(order.order_date, lang);
  const balance = Number(order.balance) || 0;

  return (
    <article className="db-order">
      <header className="db-order__top">
        <div>
          <h3>
            {t("order")} #{order.order_no}
          </h3>
          <p className="db-muted db-order__dates">
            {orderDate ? (
              <span>
                {t("orderDate")}: {orderDate}
              </span>
            ) : null}
            {readyDate ? (
              <span>
                {t("deliveryDate")}: {readyDate}
              </span>
            ) : null}
          </p>
        </div>
        <div className="db-order__aside">
          <span className={`db-badge db-badge--${order.status}`}>{t(STATUS_LABEL[order.status])}</span>
          {order.image_url ? (
            // eslint-disable-next-line @next/next/no-img-element
            <img
              className="db-order__thumb"
              src={order.image_url}
              alt=""
              loading="lazy"
              onError={(e) => {
                e.currentTarget.style.display = "none";
              }}
            />
          ) : null}
        </div>
      </header>

      <ol className="db-stepper" aria-label={t(STATUS_LABEL[order.status])}>
        {STATUS_STEPS.map((step, index) => {
          const state = index < currentIndex ? "is-done" : index === currentIndex ? "is-current" : "";
          return (
            <li key={step} className={`db-step ${state}`.trim()}>
              <span className="db-step__dot" />
              <span className="db-step__label">{t(STATUS_LABEL[step])}</span>
            </li>
          );
        })}
      </ol>

      <div className="db-suit-list">
        <p className="db-suit-list__label">{t("suits")}</p>
        {order.articles.length ? (
          order.articles.map((suit, index) => {
            const design = usableDesign(suit.design);
            return (
              <div className="db-suit-row" key={`${order.order_no}-${index}`}>
                <div>
                  <strong>{suit.name}</strong>
                  {design ? (
                    <small>
                      {t("design")}: {design}
                    </small>
                  ) : null}
                  {suit.remarks ? (
                    <small>
                      {t("remarks")}: {suit.remarks}
                    </small>
                  ) : null}
                  {suit.rate ? <small>{formatMoney(suit.rate)}</small> : null}
                </div>
                <span className={`db-badge db-badge--${suit.status}`}>{t(STATUS_LABEL[suit.status])}</span>
              </div>
            );
          })
        ) : (
          <p className="db-muted">{t("articles")}: —</p>
        )}
      </div>

      {Number(order.discount) > 0 ? (
        <p className="db-order__discount">
          {t("discount")}: {formatMoney(order.discount)}
        </p>
      ) : null}

      <div className="db-money">
        <div>
          <span>{t("total")}</span>
          <strong>{formatMoney(order.total)}</strong>
        </div>
        <div>
          <span>{t("paid")}</span>
          <strong>{formatMoney(order.paid)}</strong>
        </div>
        <div className={balance > 0 ? "is-due" : "is-clear"}>
          <span>{t("balance")}</span>
          <strong>{balance > 0 ? formatMoney(balance) : t("paidInFull")}</strong>
        </div>
      </div>
    </article>
  );
}
