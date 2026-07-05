"use client";

import { useEffect, useState } from "react";

type TickerItem = {
  id: string;
  label: string;
  value: string;
  delta: string;
  direction: "up" | "down";
  description?: string;
  tooltip?: string;
};

export default function Ticker() {
  const [items, setItems] = useState<TickerItem[]>([]);

  useEffect(() => {
    const loadData = async () => {
      try {
        const res = await fetch("/data/ticker.json");
        if (!res.ok) {
          console.error("Error al cargar ticker.json", res.status);
          return;
        }
        const data = (await res.json()) as TickerItem[];
        setItems(data);
      } catch (err) {
        console.error("Error de fetch en ticker.json", err);
      }
    };

    loadData();
  }, []);

  if (!items.length) {
    // Puedes mostrar un fallback o nada mientras carga
    return null;
  }

  return (
    <div className="ticker">
      <div className="ticker-track" id="ticker">
        {items.map((item) => (
          <div key={item.id} className="ticker-item">
            <span className="sym">{item.label}</span>
            <span className={item.direction === "up" ? "up" : "dn"}>
              {item.value}
            </span>
            <span className={item.direction === "up" ? "up" : "dn"}>
              {item.delta}
            </span>
          </div>
        ))}
      </div>
    </div>
  );
}
