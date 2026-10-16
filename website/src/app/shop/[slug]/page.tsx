"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { api, ProductDetail } from "@/lib/api";
import { AddToCartButton } from "@/components/AddToCartButton";
import { pickLocale, useI18n } from "@/lib/i18n";

export default function ProductPage({ params }: { params: Promise<{ slug: string }> }) {
  const { t, lang } = useI18n();
  const [product, setProduct] = useState<ProductDetail | null>(null);
  const [slug, setSlug] = useState("");
  const [missing, setMissing] = useState(false);

  useEffect(() => {
    params.then((p) => setSlug(p.slug));
  }, [params]);

  useEffect(() => {
    if (!slug) return;
    api.product(slug).then((res) => {
      if (res.ok) setProduct(res.product);
      else setMissing(true);
    });
  }, [slug]);

  if (missing) {
    return (
      <section className="db-section">
        <div className="db-wrap">
          <div className="db-empty">{t("emptyShop")}</div>
          <p style={{ marginTop: "1rem" }}>
            <Link href="/shop">{t("back")}</Link>
          </p>
        </div>
      </section>
    );
  }

  if (!product) {
    return (
      <section className="db-section">
        <div className="db-wrap">
          <p className="db-muted">{t("loading")}</p>
        </div>
      </section>
    );
  }

  const name = pickLocale(lang, "name_en", "name_ur", product);
  const desc =
    pickLocale(lang, "description_en", "description_ur", product) ||
    pickLocale(lang, "short_desc_en", "short_desc_ur", product);
  const image = product.images?.[0]?.image_url || product.image || "";

  return (
    <section className="db-section">
      <div className="db-wrap">
        <p style={{ marginBottom: "1rem" }}>
          <Link href="/shop" className="db-muted">
            ← {t("navShop")}
          </Link>
        </p>
        <div className="db-pdp">
          <div className="db-pdp__media">
            {image ? (
              // eslint-disable-next-line @next/next/no-img-element
              <img src={image} alt={name} />
            ) : null}
          </div>
          <div>
            <h1 className="db-page-title">{name}</h1>
            {product.sku ? (
              <p className="db-muted">
                {t("sku")}: {product.sku}
              </p>
            ) : null}
            <p
              className="db-product__price"
              style={{ fontSize: "1.5rem", margin: "0.75rem 0 1rem" }}
            >
              PKR {Number(product.price).toLocaleString()}
            </p>
            <p style={{ color: "var(--db-ink-soft)", marginBottom: "1.25rem" }}>{desc}</p>
            <AddToCartButton productId={product.id} name={name} price={Number(product.price)} />
          </div>
        </div>
      </div>
    </section>
  );
}
