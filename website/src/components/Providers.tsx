"use client";

import { LanguageProvider } from "@/lib/i18n";
import { SiteFooter, SiteHeader } from "@/components/SiteChrome";
import { MobileTabBar } from "@/components/MobileTabBar";
import { api } from "@/lib/api";
import { useEffect, useState } from "react";

export function Providers({ children }: { children: React.ReactNode }) {
  const [contact, setContact] = useState<{ phone?: string; whatsapp?: string }>({});

  useEffect(() => {
    api.home().then((res) => {
      if (res.ok) {
        setContact({
          phone: res.settings.contact_phone || undefined,
          whatsapp: res.settings.contact_whatsapp || undefined,
        });
      }
    });
  }, []);

  return (
    <LanguageProvider>
      <div className="db-shell">
        <SiteHeader />
        <main className="db-main">{children}</main>
        <SiteFooter phone={contact.phone} whatsapp={contact.whatsapp} />
        <MobileTabBar />
      </div>
    </LanguageProvider>
  );
}
