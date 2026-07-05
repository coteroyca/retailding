"use client";

import { useEffect, useState } from "react";

type Direction = "up" | "down" | "neutral";

type TickerItem = {
  id: string;
  label: string;
  value: string;
  delta: string;
  direction?: Direction; // opcional, default: neutral
  description?: string;
  tooltip?: string;
};

function getDirection(item: TickerItem): Direction {
  // Si no hay direction en el JSON, asumimos "neutral"
  return item.direction ?? "neutral";
}

function directionClass(direction: Direction) {
  switch (direction) {
    case "up":
      return "up"; // clase verde (ya definida en tu CSS)
    case "down":
      return "dn"; // clase roja (ya definida en tu CSS)
    case "neutral":
    default:
      return "text-white"; // color neutro/blanco
  }
}

function directionSymbol(direction: Direction) {
  switch (direction) {
    case "up":
      return "▲";
    case "down":
      return "▼";
    case "neutral":
    default:
      return ""; // sin icono
  }
}

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

  if (!items.length) return null;

  return (
    <div className="ticker">
      <div className="ticker-track" id="ticker">
        {items.map((item) => {
          const direction = getDirection(item);

          return (
            <div key={item.id} className="ticker-item">
              <span className="sym">{item.label}</span>

              {/* Valor principal */}
              <span className={directionClass(direction)}>
                {item.value}
              </span>

              {/* Delta con símbolo si:
                  - delta no está vacío
                  - y la dirección no es neutral */}
              {item.delta && direction !== "neutral" && (
                <span className={directionClass(direction)}>
                  {directionSymbol(direction)} {item.delta}
                </span>
              )}
            </div>
          );
        })}
      </div>
    </div>
  );
}
