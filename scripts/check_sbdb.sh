#!/usr/bin/env bash
# scripts/check_sbdb.sh
# Quick helper to test SBDB lookups (des= and sstr=) and extract phys_par values (M1/K1/G)
# Usage: ./scripts/check_sbdb.sh "C/1972 E1"

set -euo pipefail
query="$1"
if [ -z "$query" ]; then
  echo "Usage: $0 \"designation or name\""
  exit 1
fi

# urlencode helper using python3
urlencode() {
  python3 -c "import urllib.parse,sys;print(urllib.parse.quote(sys.argv[1]))" "$1"
}

variants=()
variants+=("$query")
# strip parentheses
no_paren=$(echo "$query" | sed -E 's/\s*\(.*\)\s*//')
if [ "$no_paren" != "$query" ]; then
  variants+=("$no_paren")
fi
# normalized spacing
norm=$(echo "$no_paren" | tr -s ' /' ' ')
variants+=("$norm")
variants+=("$(echo "$norm" | tr -d ' ')")
# add C/ and P/ prefixes if not already
if [[ ! $no_paren =~ ^[CP]/ ]]; then
  variants+=("C/$no_paren")
  variants+=("P/$no_paren")
fi
# add name tokens
IFS=' /' read -r -a parts <<< "$query"
if [ ${#parts[@]} -gt 1 ]; then
  tail_tokens=()
  for p in "${parts[@]}"; do
    if [[ $p =~ [A-Za-z] ]]; then
      tail_tokens+=("$p")
    fi
  done
  if [ ${#tail_tokens[@]} -gt 0 ]; then
    variants+=("${tail_tokens[*]}")
    variants+=("$(printf "%s" "${tail_tokens[*]}" | tr -d ' ')")
  fi
fi

# deduplicate while preserving order
declare -A seen
cands=()
for v in "${variants[@]}"; do
  vtrim=$(echo "$v" | sed -E 's/^\s+|\s+$//g')
  if [ -z "$vtrim" ]; then
    continue
  fi
  if [ -z "${seen[$vtrim]:-}" ]; then
    seen[$vtrim]=1
    cands+=("$vtrim")
  fi
done

echo "Testing SBDB lookup variants for: $query"

found_any=0
for cq in "${cands[@]}"; do
  enc=$(urlencode "$cq")
  for mode in des sstr; do
    url="https://ssd-api.jpl.nasa.gov/sbdb.api?${mode}=${enc}&phys-par=1"
    echo "\n-> Trying ${mode}=${cq}"
    body=$(curl -sS "$url" || true)
    if [ -z "$body" ]; then
      echo "  (no response)"
      continue
    fi
    has_obj=$(echo "$body" | jq 'has("object") or has("orbit")' 2>/dev/null || echo false)
    if [ "$has_obj" = "true" ]; then
      found_any=1
      echo "  SBDB record found (mode=${mode})"
    else
      echo "  No object/orbit in response"
    fi

    # Extract phys_par values: M1, K1, M2, K2, G
    m1=$(echo "$body" | jq -r '(.phys_par[]? | select(.name=="M1") | .value) // empty' 2>/dev/null || echo '')
    k1=$(echo "$body" | jq -r '(.phys_par[]? | select(.name=="K1") | .value) // empty' 2>/dev/null || echo '')
    m2=$(echo "$body" | jq -r '(.phys_par[]? | select(.name=="M2") | .value) // empty' 2>/dev/null || echo '')
    k2=$(echo "$body" | jq -r '(.phys_par[]? | select(.name=="K2") | .value) // empty' 2>/dev/null || echo '')
    gval=$(echo "$body" | jq -r '(.phys_par[]? | select(.name=="G") | .value) // empty' 2>/dev/null || echo '')

    if [ -n "$m1" ] || [ -n "$k1" ] || [ -n "$m2" ] || [ -n "$k2" ] || [ -n "$gval" ]; then
      echo "  phys_par found:"
      [ -n "$m1" ] && echo "    M1 (total H) = $m1"
      [ -n "$k1" ] && echo "    K1 (slope n) = $k1"
      [ -n "$m2" ] && echo "    M2 (nuclear H) = $m2"
      [ -n "$k2" ] && echo "    K2 (nuclear slope) = $k2"
      [ -n "$gval" ] && echo "    G (phase) = $gval"
      # print a short object fullname if present
      fullname=$(echo "$body" | jq -r '.object.fullname // empty' 2>/dev/null || echo '')
      if [ -n "$fullname" ]; then
        echo "    object fullname: $fullname"
      fi
    else
      echo "  No phys_par (M1/K1/etc.) in response"
    fi

    # If we found an object, stop further queries for brevity
    if [ "$has_obj" = "true" ]; then
      # break out of inner loop
      break 2
    fi
  done
done

if [ $found_any -eq 0 ]; then
  echo "\nNo SBDB record found for any variant of: $query"
  exit 2
fi

echo "\nDone."
