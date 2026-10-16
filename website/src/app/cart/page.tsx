"use client";

import { FormEvent, useState } from "react";
import { api } from "@/lib/api";
import { useCart } from "@/components/AddToCartButton";
import { useI18n } from "@/lib/i18n";

export default function CartPage() {
  const { t } = useI18n();
  const { items, save, clear } = useCart();
  const [name, setName] = useState("");
  const [phone, setPhone] = useState("");
  const [address, setAddress] = useState("");
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [busy, setBusy] = useState(false);
  const total = items.reduce((sum, i) => sum + i.price * i.qty, 0);

  async function checkout(e: FormEvent) {
    e.preventDefault();
    if (!items.length) return;
    setBusy(true);
    setError("");
    setMessage("");
    const res = await api.checkout({
      items: items.map((i) => ({ product_id: i.product_id, qty: i.qty })),
      name,
      phone,
      address,
      payment_method: "cod",
    });
    setBusy(false);
    if (!res.ok) {
      setError(t("notFoundCustomer"));
      return;
    }
    clear();
    setMessage(`${t("orderPlaced")} ${res.order_number}`);
  }

  return (
    <section className="db-section">
      <div className="db-wrap" style={{ maxWidth: 720 }}>
        <h1 className="db-page-title">{t("cartTitle")}</h1>
        {error ? <div className="db-alert db-alert--error">{error}</div> : null}
        {message ? <div className="db-alert db-alert--ok">{message}</div> : null}
        {!items.length && !message ? <div className="db-empty">{t("cartEmpty")}</div> : null}
        {items.length ? (
          <>
            <div className="db-panel" style={{ marginBottom: "1rem" }}>
              {items.map((item) => (
                <div
                  key={item.product_id}
                  style={{
                    display: "flex",
                    justifyContent: "space-between",
                    gap: "1rem",
                    padding: "0.75rem 0",
                    borderBottom: "1px solid var(--db-line)",
                  }}
                >
                  <div>
                    <strong>{item.name}</strong>
                    <div className="db-muted">
                      × {item.qty} · PKR {item.price.toLocaleString()}
                    </div>
                  </div>
                  <button
                    type="button"
                    className="db-btn db-btn--secondary"
                    onClick={() => save(items.filter((i) => i.product_id !== item.product_id))}
                  >
                    {t("remove")}
                  </button>
                </div>
              ))}
              <p className="db-product__price" style={{ marginTop: "1rem" }}>
                {t("total")}: PKR {total.toLocaleString()}
              </p>
            </div>
            <form className="db-panel" onSubmit={checkout}>
              <p className="db-hint" style={{ marginTop: 0 }}>
                {t("paymentCod")}
              </p>
              <div className="db-field">
                <label htmlFor="name">{t("fullName")}</label>
                <input id="name" value={name} onChange={(e) => setName(e.target.value)} required />
              </div>
              <div className="db-field">
                <label htmlFor="phone">{t("phone")}</label>
                <input id="phone" value={phone} onChange={(e) => setPhone(e.target.value)} required />
              </div>
              <div className="db-field">
                <label htmlFor="address">{t("address")}</label>
                <textarea
                  id="address"
                  rows={3}
                  value={address}
                  onChange={(e) => setAddress(e.target.value)}
                  required
                />
              </div>
              <button className="db-btn db-btn--primary" disabled={busy} type="submit">
                {busy ? t("checking") : t("checkout")}
              </button>
            </form>
          </>
        ) : null}
      </div>
    </section>
  );
}
