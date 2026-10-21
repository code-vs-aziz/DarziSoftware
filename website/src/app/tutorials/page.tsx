"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { api, MeasurementGroup } from "@/lib/api";
import { useI18n } from "@/lib/i18n";

export default function TutorialsPage() {
  const { t, lang } = useI18n();
  const [catalog, setCatalog] = useState<MeasurementGroup[]>([]);

  useEffect(() => {
    api.measurementCatalog().then((res) => {
      if (res.ok) setCatalog(res.catalog);
    });
  }, []);

  return (
    <section className="db-section">
      <div className="db-wrap">
        <h1 className="db-page-title">{t("navTutorials")}</h1>
        <p className="db-lead">{t("tutorialsLead")}</p>
        <div className="db-grid db-grid--cards">
          {catalog.map((group) => (
            <article className="db-path-card" key={group.garment}>
              <h3>{lang === "ur" ? group.label_ur : group.label_en}</h3>
              <ul style={{ margin: "0.5rem 0 0", paddingInlineStart: "1.1rem", color: "var(--db-muted)" }}>
                {group.fields.slice(0, 6).map((f) => (
                  <li key={f.key}>{lang === "ur" ? f.label_ur : f.label_en}</li>
                ))}
              </ul>
            </article>
          ))}
        </div>
        <p style={{ marginTop: "1.5rem" }}>
          <Link href="/measurements" className="db-btn db-btn--primary">
            {t("ctaMeasure")}
          </Link>
        </p>
      </div>
    </section>
  );
}
