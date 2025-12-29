#!/usr/bin/env python3
"""DXF → SVG converter used by the ICAS map pipeline.

The converter is intentionally lightweight: it renders the provided DXF layout
directly to SVG using ezdxf's drawing add-ons so it can run inside the PHP
application container without auxiliary services. Only modelspace content is
rendered today, which covers the current facility plans.
"""

from __future__ import annotations

import argparse
import sys
from pathlib import Path

import ezdxf
from ezdxf.addons.drawing import Frontend, RenderContext, layout as drawing_layout, properties as drawing_properties, svg
from ezdxf.recover import readfile as recover_readfile


def parse_args() -> argparse.Namespace:
  parser = argparse.ArgumentParser(description="Convert a DXF blueprint to SVG")
  parser.add_argument("input", help="Absolute path to the DXF/DFX file")
  parser.add_argument("output", help="Absolute path where the SVG should be saved")
  parser.add_argument(
    "--layout",
    default="model",
    help="Layout name to render (defaults to modelspace)",
  )
  parser.add_argument(
    "--background",
    default="#0b1220",
    help="Background color for the SVG viewbox (hex, default: #0b1220)",
  )
  parser.add_argument(
    "--line-color",
    default="#f8fafc",
    help="Fallback stroke color when the DXF layer has no explicit color",
  )
  return parser.parse_args()


def load_document(path: Path):
  try:
    return ezdxf.readfile(path)
  except ezdxf.DXFStructureError:
    doc, auditor = recover_readfile(path)
    if auditor.has_errors:
      raise RuntimeError(f"Unable to repair DXF: {path}")
    return doc


def resolve_layout(doc, layout_name: str):
  normalized_name = (layout_name or "").strip().lower()
  if normalized_name in {"", "model", "modelspace", "model space"}:
    return doc.modelspace()

  for layout_obj in doc.layouts:
    if layout_obj.name.lower() == normalized_name:
      return doc.layout(layout_obj.name)

  raise ValueError(f"Layout '{layout_name}' was not found in the DXF")


def render_svg(
  doc,
  layout_name: str,
  background: str,
  default_line: str,
) -> str:
  ctx = RenderContext(doc)
  source_layout = resolve_layout(doc, layout_name)

  backend = svg.SVGBackend()
  backend.set_background(background)

  layout_props = drawing_properties.LayoutProperties.from_layout(source_layout)
  layout_props.set_colors(background, default_line)

  frontend = Frontend(ctx, backend)
  frontend.draw_layout(source_layout, finalize=True, layout_properties=layout_props)

  svg_page = drawing_layout.Page(0, 0, units=drawing_layout.Units.mm)
  return backend.get_string(svg_page)


def main() -> int:
  args = parse_args()
  input_path = Path(args.input).expanduser().resolve()
  output_path = Path(args.output).expanduser().resolve()
  output_path.parent.mkdir(parents=True, exist_ok=True)

  try:
    doc = load_document(input_path)
    svg_markup = render_svg(
      doc=doc,
      layout_name=args.layout,
      background=args.background,
      default_line=args.line_color,
    )
    output_path.write_text(svg_markup, encoding="utf-8")
    return 0
  except Exception as exc:  # pragma: no cover - runtime safeguard
    if output_path.exists():
      output_path.unlink()
    print(f"DXF conversion failed: {exc}", file=sys.stderr)
    return 1


if __name__ == "__main__":  # pragma: no cover
  sys.exit(main())
