"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { useI18n } from "@/lib/i18n";

export const CART_KEY = "db_cart";

export type CartItem = { product_id: number; name: string; price: number; qty: number };

function readCart(): CartItem[] {
  try {
    return JSON.parse(localStorage.getItem(CART_KEY) || "[]") as CartItem[];
  } catch {
    return [];
  }
}

export function AddToCartButton({
  productId,
  name,
  price,
}: {
  productId: number;
  name: string;
  price: number;
}) {
  const { t } = useI18n();
  const [added, setAdded] = useState(false);

  function add() {
    const cart = readCart();
    const existing = cart.find((i) => i.product_id === productId);
    if (existing) existing.qty += 1;
    else cart.push({ product_id: productId, name, price, qty: 1 });
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    window.dispatchEvent(new Event("db-cart-updated"));
    setAdded(true);
  }

  return (
    <div style={{ display: "flex", gap: "0.75rem", flexWrap: "wrap" }}>
      <button type="button" className="db-btn db-btn--primary" onClick={add}>
        {added ? "✓" : t("addToCart")}
      </button>
      <Link href="/cart" className="db-btn db-btn--secondary">
        {t("viewCart")}
      </Link>
    </div>
  );
}

export function useCart() {
  const [items, setItems] = useState<CartItem[]>([]);
  useEffect(() => {
    setItems(readCart());
  }, []);
  function save(next: CartItem[]) {
    setItems(next);
    localStorage.setItem(CART_KEY, JSON.stringify(next));
    window.dispatchEvent(new Event("db-cart-updated"));
  }
  return { items, save, clear: () => save([]) };
}
