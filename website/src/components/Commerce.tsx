"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { ProductCard as ProductCardType } from "@/lib/api";
import { pickLocale, useI18n } from "@/lib/i18n";

type Slide = {
  title: string;
  subtitle: string;
  image?: string;
  ctaShop: string;
  ctaMeasure: string;
};

export function HeroCarousel({
  slides,
  products,
}: {
  slides: Slide[];
  products: ProductCardType[];
}) {
  const { t, lang } = useI18n();
  const featured = products.filter((p) => Number(p.is_featured) === 1).slice(0, 4);
  const items =
    featured.length > 0
      ? featured.map((p) => ({
          title: pickLocale(lang, "name_en", "name_ur", p),
          subtitle: pickLocale(lang, "short_desc_en", "short_desc_ur", p) || t("heroSubtitle"),
          image: p.image,
          href: `/shop/${p.slug}`,
          price: p.price,
        }))
      : slides.map((s) => ({
          title: s.title,
          subtitle: s.subtitle,
          image: s.image || "/media/7.jpg",
          href: "/shop",
          price: null as number | null,
        }));

  const [index, setIndex] = useState(0);

  useEffect(() => {
    if (items.length < 2) return;
    const id = window.setInterval(() => setIndex((i) => (i + 1) % items.length), 5200);
    return () => window.clearInterval(id);
  }, [items.length]);

  return (
    <section className="db-hero" aria-label="Featured" dir="ltr">
      <div
        className="db-hero__track"
        style={{ transform: `translateX(-${index * 100}%)` }}
      >
        {items.map((item, i) => (
          <div className="db-hero__slide" key={`${item.title}-${i}`}>
            <div className="db-wrap db-hero__grid">
              <div>
                <span className="db-hero__eyebrow">{t("featuredBadge")}</span>
                <h1>{item.title}</h1>
                <p>{item.subtitle}</p>
                <div className="db-hero__actions">
                  <Link href={item.href} className="db-btn db-btn--primary">
                    {t("ctaShop")}
                  </Link>
                  <Link href="/measurements" className="db-btn db-btn--secondary">
                    {t("ctaMeasure")}
                  </Link>
                </div>
              </div>
              <div className="db-hero__visual">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img src={item.image || "/media/7.jpg"} alt="" />
              </div>
            </div>
          </div>
        ))}
      </div>
      {items.length > 1 ? (
        <div className="db-hero__dots">
          {items.map((_, i) => (
            <button
              key={i}
              type="button"
              className={i === index ? "is-active" : ""}
              aria-label={`Slide ${i + 1}`}
              onClick={() => setIndex(i)}
            />
          ))}
        </div>
      ) : null}
    </section>
  );
}

export function ProductCard({ product }: { product: ProductCardType }) {
  const { t, lang } = useI18n();
  const name = pickLocale(lang, "name_en", "name_ur", product);

  return (
    <Link href={`/shop/${product.slug}`} className="db-product">
      <div className="db-product__media">
        {Number(product.is_featured) === 1 ? (
          <span className="db-product__badge">{t("featuredBadge")}</span>
        ) : null}
        {product.image ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img src={product.image} alt={name} />
        ) : null}
      </div>
      <div className="db-product__body">
        <div className="db-product__name">{name}</div>
        <div className="db-product__price">
          PKR {Number(product.price).toLocaleString()}
        </div>
      </div>
    </Link>
  );
}
