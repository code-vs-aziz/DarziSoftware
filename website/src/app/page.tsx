"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { api, ProductCard as ProductType, HomeSettings } from "@/lib/api";
import { HeroCarousel, ProductCard } from "@/components/Commerce";
import { pickLocale, useI18n } from "@/lib/i18n";

type Category = {
  id: number;
  slug: string;
  name_en: string;
  name_ur: string;
};

export default function HomePage() {
  const { t, lang } = useI18n();
  const [products, setProducts] = useState<ProductType[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [settings, setSettings] = useState<HomeSettings | null>(null);

  useEffect(() => {
    Promise.all([api.home(), api.products(), api.categories()]).then(([home, all, cats]) => {
      if (home.ok) setSettings(home.settings);
      if (all.ok) setProducts(all.products);
      if (cats.ok) setCategories(cats.categories);
    });
  }, []);

  const featured = products.filter((p) => Number(p.is_featured) === 1);
  const arrivals = products.slice(0, 8);
  const popular = [...products].reverse().slice(0, 4);

  return (
    <>
      <HeroCarousel
        products={featured.length ? featured : products}
        slides={[
          {
            title: t("heroTitle"),
            subtitle: t("heroSubtitle"),
            image: "/media/7.jpg",
            ctaShop: t("ctaShop"),
            ctaMeasure: t("ctaMeasure"),
          },
          {
            title: settings?.site_name || t("brand"),
            subtitle:
              (settings &&
                pickLocale(lang, "tagline_en", "tagline_ur", {
                  tagline_en: settings.tagline_en || "",
                  tagline_ur: settings.tagline_ur || "",
                })) ||
              t("heroSubtitle"),
            image: "/media/1.jpg",
            ctaShop: t("ctaShop"),
            ctaMeasure: t("ctaMeasure"),
          },
        ]}
      />

      <section className="db-section db-section--tight">
        <div className="db-wrap">
          <div className="db-section__head">
            <h2>{t("howItWorksTitle")}</h2>
          </div>
          <div className="db-dual">
            <article className="db-path-card">
              <h3>{t("howShop")}</h3>
              <p>{t("howShopBody")}</p>
              <Link href="/shop" className="db-btn db-btn--primary">
                {t("ctaShop")}
              </Link>
            </article>
            <article className="db-path-card">
              <h3>{t("howMeasure")}</h3>
              <p>{t("howMeasureBody")}</p>
              <Link href="/measurements" className="db-btn db-btn--secondary">
                {t("ctaMeasure")}
              </Link>
            </article>
          </div>
        </div>
      </section>

      <section className="db-section" id="categories">
        <div className="db-wrap">
          <div className="db-section__head">
            <h2>{t("byCategory")}</h2>
            <Link href="/shop">{t("viewAll")}</Link>
          </div>
          {categories.length ? (
            <div className="db-cat-row">
              {categories.map((c) => (
                <Link key={c.id} href={`/shop?category=${c.id}`} className="db-cat">
                  <strong>{pickLocale(lang, "name_en", "name_ur", c)}</strong>
                </Link>
              ))}
            </div>
          ) : (
            <div className="db-empty">{t("emptyShop")}</div>
          )}
        </div>
      </section>

      <section className="db-section" style={{ paddingTop: 0 }}>
        <div className="db-wrap">
          <div className="db-section__head">
            <h2>{t("newArrivals")}</h2>
            <Link href="/shop">{t("viewAll")}</Link>
          </div>
          {arrivals.length ? (
            <div className="db-grid">
              {arrivals.map((p) => (
                <ProductCard key={p.id} product={p} />
              ))}
            </div>
          ) : (
            <div className="db-empty">{t("emptyShop")}</div>
          )}
        </div>
      </section>

      {featured.length ? (
        <section className="db-section" style={{ paddingTop: 0 }}>
          <div className="db-wrap">
            <div className="db-section__head">
              <h2>{t("featured")}</h2>
            </div>
            <div className="db-grid">
              {featured.map((p) => (
                <ProductCard key={`f-${p.id}`} product={p} />
              ))}
            </div>
          </div>
        </section>
      ) : null}

      {popular.length ? (
        <section className="db-section" style={{ paddingTop: 0 }}>
          <div className="db-wrap">
            <div className="db-section__head">
              <h2>{t("popular")}</h2>
            </div>
            <div className="db-grid">
              {popular.map((p) => (
                <ProductCard key={`p-${p.id}`} product={p} />
              ))}
            </div>
          </div>
        </section>
      ) : null}
    </>
  );
}
