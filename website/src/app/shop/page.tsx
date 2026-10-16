"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { useSearchParams } from "next/navigation";
import { api, Category, ProductCard as ProductType } from "@/lib/api";
import { ProductCard } from "@/components/Commerce";
import { pickLocale, useI18n } from "@/lib/i18n";
import { Suspense } from "react";

function ShopInner() {
  const { t, lang } = useI18n();
  const params = useSearchParams();
  const categoryId = params.get("category") || "";
  const [products, setProducts] = useState<ProductType[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);

  useEffect(() => {
    const q = categoryId ? `?category_id=${categoryId}` : "";
    Promise.all([api.products(q), api.categories()]).then(([p, c]) => {
      if (p.ok) setProducts(p.products);
      if (c.ok) setCategories(c.categories);
    });
  }, [categoryId]);

  return (
    <section className="db-section">
      <div className="db-wrap">
        <h1 className="db-page-title">{t("navShop")}</h1>
        <p className="db-lead">{t("heroSubtitle")}</p>

        <div id="categories" className="db-cat-row db-cat-row--chips" style={{ marginBottom: "1.5rem" }}>
          <Link href="/shop" className={`db-cat${!categoryId ? " is-active" : ""}`} aria-current={!categoryId ? "page" : undefined}>
            <strong>{t("viewAll")}</strong>
          </Link>
          {categories.map((c) => (
            <Link
              key={c.id}
              href={`/shop?category=${c.id}`}
              className={`db-cat${categoryId === String(c.id) ? " is-active" : ""}`}
              aria-current={categoryId === String(c.id) ? "page" : undefined}
            >
              <strong>{pickLocale(lang, "name_en", "name_ur", c)}</strong>
            </Link>
          ))}
        </div>

        {products.length ? (
          <div className="db-grid">
            {products.map((p) => (
              <ProductCard key={p.id} product={p} />
            ))}
          </div>
        ) : (
          <div className="db-empty">{t("emptyShop")}</div>
        )}
      </div>
    </section>
  );
}

export default function ShopPage() {
  return (
    <Suspense fallback={<div className="db-wrap db-section">{/* loading */}</div>}>
      <ShopInner />
    </Suspense>
  );
}
