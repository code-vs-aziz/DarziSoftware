"use client";

import Image from "next/image";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useState } from "react";
import { useI18n } from "@/lib/i18n";
import { CART_KEY } from "@/components/AddToCartButton";

export function SiteHeader() {
  const { t, lang, setLang } = useI18n();
  const pathname = usePathname();
  const [cartCount, setCartCount] = useState(0);

  useEffect(() => {
    const sync = () => {
      try {
        const items = JSON.parse(localStorage.getItem(CART_KEY) || "[]") as { qty: number }[];
        setCartCount(items.reduce((n, i) => n + (i.qty || 0), 0));
      } catch {
        setCartCount(0);
      }
    };
    sync();
    window.addEventListener("storage", sync);
    window.addEventListener("db-cart-updated", sync);
    return () => {
      window.removeEventListener("storage", sync);
      window.removeEventListener("db-cart-updated", sync);
    };
  }, [pathname]);

  const links = [
    { href: "/", label: t("navHome") },
    { href: "/shop", label: t("navShop") },
    { href: "/shop#categories", label: t("navCategories") },
    { href: "/measurements", label: t("navMeasurements") },
    { href: "/contact", label: t("navContact") },
  ];

  return (
    <header className="db-topbar">
      <div className="db-wrap db-topbar__row">
        <Link href="/" className="db-logo" aria-label={t("brand")}>
          <Image
            src="/darzi-baba-logo.png"
            alt={t("brand")}
            width={180}
            height={48}
            priority
          />
          <span>{t("brand")}</span>
        </Link>

        <nav className="db-nav-desktop" aria-label="Primary">
          {links.map((l) => (
            <Link
              key={l.href}
              href={l.href}
              aria-current={pathname === l.href ? "page" : undefined}
            >
              {l.label}
            </Link>
          ))}
        </nav>

        <div className="db-header-actions">
          <div className="db-lang" role="group" aria-label="Language">
            <button
              type="button"
              className={lang === "en" ? "is-active" : ""}
              onClick={() => setLang("en")}
            >
              EN
            </button>
            <button
              type="button"
              className={lang === "ur" ? "is-active" : ""}
              onClick={() => setLang("ur")}
            >
              اردو
            </button>
          </div>

          <Link
            href="/measurements"
            className="db-icon-btn db-icon-btn--desktop-only"
            aria-label={t("navMeasurements")}
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden>
              <path
                d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4 0-7 2-7 4.5V20h14v-1.5C19 16 16 14 12 14Z"
                stroke="currentColor"
                strokeWidth="1.7"
              />
            </svg>
          </Link>

          <Link
            href="/cart"
            className="db-icon-btn db-icon-btn--desktop-only"
            aria-label={t("navCart")}
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden>
              <path
                d="M6 6h15l-1.5 9h-12z"
                stroke="currentColor"
                strokeWidth="1.7"
                strokeLinejoin="round"
              />
              <path d="M6 6 5 3H2" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" />
              <circle cx="9" cy="20" r="1.3" fill="currentColor" />
              <circle cx="17" cy="20" r="1.3" fill="currentColor" />
            </svg>
            {cartCount > 0 ? <span className="badge">{cartCount}</span> : null}
          </Link>
        </div>
      </div>
    </header>
  );
}

export function SiteFooter({
  phone,
  whatsapp,
}: {
  phone?: string;
  whatsapp?: string;
}) {
  const { t } = useI18n();
  const year = new Date().getFullYear();

  return (
    <footer className="db-footer">
      <div className="db-wrap db-footer__grid">
        <div className="db-footer__brand">
          <Image src="/darzi-baba-logo.png" alt={t("brand")} width={160} height={44} />
          <p>{t("aboutBody")}</p>
        </div>
        <div>
          <h4>{t("quickLinks")}</h4>
          <ul>
            <li>
              <Link href="/">{t("navHome")}</Link>
            </li>
            <li>
              <Link href="/shop">{t("navShop")}</Link>
            </li>
            <li>
              <Link href="/measurements">{t("navMeasurements")}</Link>
            </li>
            <li>
              <Link href="/tutorials">{t("navTutorials")}</Link>
            </li>
          </ul>
        </div>
        <div>
          <h4>{t("navCategories")}</h4>
          <ul>
            <li>
              <Link href="/shop">{t("navShop")}</Link>
            </li>
            <li>
              <Link href="/shop#categories">{t("byCategory")}</Link>
            </li>
            <li>
              <Link href="/cart">{t("navCart")}</Link>
            </li>
          </ul>
        </div>
        <div>
          <h4>{t("support")}</h4>
          <ul>
            <li>
              <Link href="/contact">{t("navContact")}</Link>
            </li>
            {phone ? <li>{phone}</li> : null}
            {whatsapp ? <li>WhatsApp: {whatsapp}</li> : null}
          </ul>
        </div>
      </div>
      <div className="db-wrap db-footer__bottom">
        <span>
          © {year} {t("brand")}. {t("rights")}
        </span>
        <span>{t("poweredBy")}</span>
      </div>
    </footer>
  );
}
