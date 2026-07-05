"use client";

import { useEffect, useState } from "react";

type Direction = "up" | "down" | "neutral";
type Emphasis = "up" | "down" | "neutral";

type TickerItem = {
  id: string;
  label: string;
  value: string;
  delta: string;        // texto del delta (p.ej. "12 CUPOS")
  direction?: Direction; // controla icono ▲/▼ (opcional)
  emphasis?: Emphasis;   // controla color (opcional)
  description?: string;
  tooltip?: string;
};

// Si no se especifica direction, asumimos "neutral" (sin icono)
function getDirection(item: TickerItem): Direction {
  return item.direction ?? "neutral";
}

// Si no se especifica emphasis, asumimos "neutral" (blanco)
function getEmphasis(item: TickerItem): Emphasis {
  return item.emphasis ?? "neutral";
}

// Clase de color según emphasis
function emphasisClass(emphasis: Emphasis) {
  switch (emphasis) {
    case "up":
      return "up";      // clase verde (definida en tu CSS)
    case "down":
      return "dn";      // clase roja
    case "neutral":
    default:
      return "text-white"; // color neutro/blanco
  }
}

// Símbolo según direction (para el delta, no para el valor)
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
          const emphasis = getEmphasis(item);

          return (
            <div key={item.id} className="ticker-item">
              <span className="sym">{item.label}</span>

              {/* Valor principal: coloreado según emphasis */}
              <span className={emphasisClass(emphasis)}>
                {item.value}
              </span>

              {/* Delta:
                 - se muestra siempre que delta no esté vacío
                 - usa el mismo color (emphasis)
                 - añade símbolo solo si direction no es neutral */}
              {item.delta && item.delta.trim() !== "" && (
                <span className={emphasisClass(emphasis)}>
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
