"use client";

import { useEffect, useState } from "react";
import { api, HomeSettings } from "@/lib/api";
import { useI18n } from "@/lib/i18n";

export default function ContactPage() {
  const { t } = useI18n();
  const [settings, setSettings] = useState<HomeSettings | null>(null);

  useEffect(() => {
    api.home().then((res) => {
      if (res.ok) setSettings(res.settings);
    });
  }, []);

  return (
    <section className="db-section">
      <div className="db-wrap" style={{ maxWidth: 720 }}>
        <h1 className="db-page-title">{t("contactTitle")}</h1>
        <div className="db-panel">
          <p>
            <strong>{settings?.site_name || t("brand")}</strong>
          </p>
          {settings?.contact_phone ? (
            <p>
              {t("phone")}: {settings.contact_phone}
            </p>
          ) : null}
          {settings?.contact_whatsapp ? <p>WhatsApp: {settings.contact_whatsapp}</p> : null}
          {settings?.contact_email ? <p>{settings.contact_email}</p> : null}
          {settings?.contact_address_en || settings?.contact_address_ur ? (
            <p>{settings.contact_address_en || settings.contact_address_ur}</p>
          ) : (
            <p className="db-muted">{t("support")}</p>
          )}
        </div>
      </div>
    </section>
  );
}
