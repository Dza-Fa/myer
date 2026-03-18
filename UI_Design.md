# UI Design System - Myer

## 1. Design Principles
- **Clarity over decoration**
- **Consistent spacing/hierarchy**
- **Color = meaning**
- **Low cognitive load**

## 2. Color System

### Primary (Brand)
| Variant | Hex |
|---------|-----|
| Primary | #2563EB |
| Hover | #1D4ED8 |
| Light | #DBEAFE |

### Financial
| Semantic | Hex |
|----------|-----|
| Income | #16A34A |
| Expense | #DC2626 |
| Warning | #F59E0B |
| Neutral | #6B7280 |

### Background
| Element | Hex |
|---------|-----|
| Background | #F9FAFB |
| Card | #FFFFFF |
| Border | #E5E7EB |

## 3. Typography
**Font:** Inter

| Element | Size | Weight |
|---------|------|--------|
| H1 | 24px | bold |
| H2 | 18px | semibold |
| Body | 14px | normal |
| Small | 12px | gray |

## 4. Spacing (8px Grid)
| Size | px |
|------|----|
| Micro | 4px |
| Small | 8px |
| Standard | 16px |
| Section | 24px |
| Large | 32px |

## 5. Components

### Button
```
Primary: bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg
Secondary: border border-gray-300 text-dark bg-white
```

### Card
```
bg-white rounded-xl shadow-sm p-4
```

### Input
```
border border-gray-300 focus:border-blue-500 rounded-lg px-3 py-2
```

## 6. Charts
- Income: Green
- Expense: Red
- Max 4-5 warna

## 7. States
- **Success**: Green + message
- **Error**: Red + pesan jelas
- **Loading**: Skeleton/spinner

## 8. Tailwind Examples
```html
<div class="bg-white rounded-xl shadow-sm p-6">
  <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
    Add
  </button>
</div>
```

