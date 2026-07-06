"use client";

import { useEffect, useState } from "react";

type Direction = "up" | "down" | "neutral" | "info";

type TickerItem = {
  id: string;
  label: string;
  value: string;
  delta: string;
  direction?: Direction;
  description?: string;
  tooltip?: string;
};

// Si no hay direction en el JSON, asumimos "info"
function getDirection(item: TickerItem): Direction {
  return item.direction ?? "info";
}

// Clase de color según direction
function directionClass(direction: Direction) {
  switch (direction) {
    case "up":
      return "up";
    case "down":
      return "dn";
    case "neutral":
      return "text-white";
    case "info":
    default:
      return "text-yellow-300";
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
      return "";
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

        const raw = await res.json();

        // raw es el array que contiene tickerGlossary [file:240]
        const glossary =
          raw && Array.isArray(raw) ? raw[0]?.tickerGlossary : undefined;

        if (!glossary) {
          console.error("tickerGlossary no encontrado en ticker.json");
          return;
        }

        // glossary es un objeto cuyas claves son TICKET-PROM, DEMANDA-ARR, etc. [file:240]
        const mapped: TickerItem[] = Object.entries(glossary).map(
          ([key, g]: [
            string,
            {
              label?: string;
              displayName?: string;
              status?: string;
              valueExample?: string;
              deltaExample?: string;
              definition?: string;
              howToRead?: string;
            }
          ]) => {
            let direction: Direction = "info";
            if (g.status === "up") direction = "up";
            else if (g.status === "dn") direction = "down";

            return {
              id: key,
              label: g.label ?? key,
              value: g.valueExample ?? "",
              delta: g.deltaExample ?? "",
              direction,
              description: g.displayName,
              tooltip: g.definition,
            };
          }
        );

        setItems(mapped);
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

              {/* Delta */}
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
