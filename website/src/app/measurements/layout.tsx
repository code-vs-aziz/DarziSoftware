import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "My orders",
  robots: { index: false, follow: false },
};

export default function MeasurementsLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return children;
}
