"use client";

import { useEffect, useState } from "react";

type Direction = "up" | "down" | "neutral"| "info";

type TickerItem = {
  id: string;
  label: string;
  value: string;
  delta: string;
  direction?: Direction; // opcional, default: neutral
  description?: string;
  tooltip?: string;
};

// Si no hay direction en el JSON, asumimos "neutral"
function getDirection(item: TickerItem): Direction {
  return item.direction ?? "info";
}

// Clase de color según direction
function directionClass(direction: Direction) {
  switch (direction) {
    case "up":
      return "up";      // clase verde (definida en tu CSS)
    case "down":
      return "dn";      // clase roja (definida en tu CSS)
    case "neutral":
      return "text-white"; // neutro/blanco
    case "info":
    default:
      return "text-yellow-300"; // color cian
  }
}

// Símbolo según direction (para el delta)
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

              {/* Delta:
                 - se muestra si delta no está vacío
                 - se colorea igual que el valor
                 - añade símbolo solo si direction no es neutral */}
              {item.delta && item.delta.trim() !== "" && (
                <span className={directionClass(direction)}>
                  {direction !== "neutral"
                    ? `${directionSymbol(direction)} ${item.delta}`
                    : item.delta}
                </span>
              )}
            </div>
          );
        })}
      </div>
    </div>
  );
}
