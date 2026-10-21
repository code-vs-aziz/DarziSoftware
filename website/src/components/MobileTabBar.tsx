"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useState } from "react";
import { useI18n } from "@/lib/i18n";
import { CART_KEY } from "@/components/AddToCartButton";

type TabIconName = "home" | "shop" | "orders" | "cart" | "contact";

type Tab = {
  href: string;
  label: string;
  match: (path: string) => boolean;
  icon: TabIconName;
};

function TabIcon({ name, active }: { name: TabIconName; active: boolean }) {
  const common = {
    width: 22,
    height: 22,
    viewBox: "0 0 24 24",
    "aria-hidden": true as const,
  };

  if (name === "home") {
    return (
      <svg {...common} fill={active ? "currentColor" : "none"}>
        <path
          d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5Z"
          stroke="currentColor"
          strokeWidth={active ? 0 : 1.8}
          strokeLinejoin="round"
        />
      </svg>
    );
  }

  if (name === "shop") {
    return (
      <svg {...common} fill={active ? "currentColor" : "none"}>
        <path
          d="M4 8h16l-1.2 11H5.2L4 8Z"
          stroke="currentColor"
          strokeWidth={active ? 0 : 1.8}
          strokeLinejoin="round"
        />
        <path
          d="M8 8V6a4 4 0 0 1 8 0v2"
          stroke="currentColor"
          strokeWidth="1.8"
          strokeLinecap="round"
          fill="none"
        />
      </svg>
    );
  }

  if (name === "orders") {
    return (
      <svg {...common} fill={active ? "currentColor" : "none"}>
        <path
          d="M8 4h8a2 2 0 0 1 2 2v14l-6-3-6 3V6a2 2 0 0 1 2-2Z"
          stroke="currentColor"
          strokeWidth={active ? 0 : 1.8}
          strokeLinejoin="round"
        />
      </svg>
    );
  }

  if (name === "cart") {
    return (
      <svg {...common} fill={active ? "currentColor" : "none"}>
        <path
          d="M6 6h15l-1.5 9h-12z"
          stroke="currentColor"
          strokeWidth={active ? 0 : 1.8}
          strokeLinejoin="round"
        />
        <path
          d="M6 6 5 3H2"
          stroke="currentColor"
          strokeWidth="1.8"
          strokeLinecap="round"
          fill="none"
        />
        <circle cx="9" cy="20" r="1.35" fill="currentColor" />
        <circle cx="17" cy="20" r="1.35" fill="currentColor" />
      </svg>
    );
  }

  return (
    <svg {...common} fill={active ? "currentColor" : "none"}>
      <path
        d="M5 6.5A2.5 2.5 0 0 1 7.5 4h9A2.5 2.5 0 0 1 19 6.5v7A2.5 2.5 0 0 1 16.5 16H10l-4 3v-3H7.5A2.5 2.5 0 0 1 5 13.5v-7Z"
        stroke="currentColor"
        strokeWidth={active ? 0 : 1.8}
        strokeLinejoin="round"
      />
    </svg>
  );
}

export function MobileTabBar() {
  const { t } = useI18n();
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

  const tabs: Tab[] = [
    {
      href: "/",
      label: t("navHome"),
      match: (p) => p === "/",
      icon: "home",
    },
    {
      href: "/shop",
      label: t("navShop"),
      match: (p) => p.startsWith("/shop") || p.startsWith("/product"),
      icon: "shop",
    },
    {
      href: "/measurements",
      label: t("navMeasurements"),
      match: (p) => p.startsWith("/measurements"),
      icon: "orders",
    },
    {
      href: "/cart",
      label: t("navCart"),
      match: (p) => p.startsWith("/cart") || p.startsWith("/checkout"),
      icon: "cart",
    },
    {
      href: "/contact",
      label: t("navContact"),
      match: (p) => p.startsWith("/contact"),
      icon: "contact",
    },
  ];

  return (
    <nav className="db-tabbar" aria-label="Primary mobile">
      <div className="db-tabbar__dock">
        {tabs.map((tab) => {
          const active = tab.match(pathname);
          return (
            <Link
              key={tab.href}
              href={tab.href}
              className={`db-tabbar__item${active ? " is-active" : ""}`}
              aria-current={active ? "page" : undefined}
            >
              <span className="db-tabbar__icon">
                <TabIcon name={tab.icon} active={active} />
                {tab.icon === "cart" && cartCount > 0 ? (
                  <span className="db-tabbar__badge">{cartCount > 99 ? "99+" : cartCount}</span>
                ) : null}
              </span>
              <span className="db-tabbar__label">{tab.label}</span>
            </Link>
          );
        })}
      </div>
    </nav>
  );
}
