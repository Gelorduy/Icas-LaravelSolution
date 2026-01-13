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

  # Debug: Print layer information
  print(f"=== DXF Layer Information ===", file=sys.stderr)
  print(f"Total layers in document: {len(list(doc.layers))}", file=sys.stderr)
  for layer in doc.layers:
    print(f"  - Layer: '{layer.dxf.name}' (Color: {layer.dxf.color}, Frozen: {layer.is_frozen()}, Locked: {layer.is_locked()})", file=sys.stderr)
  
  # Debug: Print entity information by layer
  print(f"\n=== Layout Entity Information ===", file=sys.stderr)
  print(f"Layout: {layout_name}", file=sys.stderr)
  entity_count_by_layer = {}
  entity_types_by_layer = {}
  
  for entity in source_layout:
    layer_name = entity.dxf.layer if hasattr(entity.dxf, 'layer') else 'UNKNOWN'
    entity_type = entity.dxftype()
    
    entity_count_by_layer[layer_name] = entity_count_by_layer.get(layer_name, 0) + 1
    
    if layer_name not in entity_types_by_layer:
      entity_types_by_layer[layer_name] = {}
    entity_types_by_layer[layer_name][entity_type] = entity_types_by_layer[layer_name].get(entity_type, 0) + 1
  
  for layer_name in sorted(entity_count_by_layer.keys()):
    print(f"  Layer '{layer_name}': {entity_count_by_layer[layer_name]} entities", file=sys.stderr)
    for entity_type, count in sorted(entity_types_by_layer[layer_name].items()):
      print(f"    - {entity_type}: {count}", file=sys.stderr)

  backend = svg.SVGBackend()
  backend.set_background(background)

  layout_props = drawing_properties.LayoutProperties.from_layout(source_layout)
  layout_props.set_colors(background, default_line)

  frontend = Frontend(ctx, backend)
  
  # Try to render and catch any errors
  print(f"\n=== Starting Rendering ===", file=sys.stderr)
  try:
    frontend.draw_layout(source_layout, finalize=True, layout_properties=layout_props)
    print(f"Rendering completed successfully", file=sys.stderr)
  except Exception as render_error:
    print(f"ERROR during rendering: {render_error}", file=sys.stderr)
    # Try layer-by-layer to identify the problem
    print(f"\n=== Attempting Layer-by-Layer Rendering to Identify Problem ===", file=sys.stderr)
    
    # Group entities by layer
    entities_by_layer = {}
    for entity in source_layout:
      layer_name = entity.dxf.layer if hasattr(entity.dxf, 'layer') else 'UNKNOWN'
      if layer_name not in entities_by_layer:
        entities_by_layer[layer_name] = []
      entities_by_layer[layer_name].append(entity)
    
    failed_layers = []
    successful_layers = []
    
    # Test each layer separately by creating a new backend/frontend
    for layer_name, entities in sorted(entities_by_layer.items()):
      print(f"Testing layer '{layer_name}' ({len(entities)} entities)...", file=sys.stderr)
      try:
        test_backend = svg.SVGBackend()
        test_backend.set_background(background)
        test_frontend = Frontend(ctx, test_backend)
        
        for entity in entities:
          test_frontend.draw_entity(entity)
        
        print(f"  ✓ Layer '{layer_name}' OK", file=sys.stderr)
        successful_layers.append(layer_name)
      except Exception as layer_error:
        print(f"  ✗ ERROR in layer '{layer_name}': {layer_error}", file=sys.stderr)
        failed_layers.append((layer_name, str(layer_error)))
    
    print(f"\n=== Layer Test Summary ===", file=sys.stderr)
    print(f"Successful layers: {len(successful_layers)}", file=sys.stderr)
    print(f"Failed layers: {len(failed_layers)}", file=sys.stderr)
    if failed_layers:
      print(f"\nFAILED LAYERS TO FIX IN LibreCAD:", file=sys.stderr)
      for layer_name, error in failed_layers:
        print(f"  - {layer_name}: {error}", file=sys.stderr)
    
    raise

  # Calculate actual bounds from the drawing extents
  width = 1920  # Default fallback
  height = 1080  # Default fallback
  
  print(f"\n=== Calculating Dimensions ===", file=sys.stderr)
  try:
    extents = source_layout.get_extents()
    print(f"Extents valid: {extents.is_valid if extents else 'None'}", file=sys.stderr)
    if extents and extents.is_valid:
      print(f"Extents: min={extents.extmin}, max={extents.extmax}, size={extents.size}", file=sys.stderr)
      calc_width = abs(extents.size.x)
      calc_height = abs(extents.size.y)
      print(f"Calculated dimensions: {calc_width} x {calc_height}", file=sys.stderr)
      
      # Only use calculated dimensions if they're reasonable (> 1 unit)
      if calc_width > 1:
        width = calc_width
      if calc_height > 1:
        height = calc_height
      print(f"Final dimensions: {width} x {height}", file=sys.stderr)
  except Exception as e:
    # Log the error but continue with defaults
    print(f"Warning: Could not calculate extents: {e}", file=sys.stderr)
    print(f"Using default dimensions: {width} x {height}", file=sys.stderr)

  print(f"\n=== Creating SVG Page ===", file=sys.stderr)
  print(f"Page dimensions: {width} x {height} mm", file=sys.stderr)
  svg_page = drawing_layout.Page(width, height, units=drawing_layout.Units.mm)
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
