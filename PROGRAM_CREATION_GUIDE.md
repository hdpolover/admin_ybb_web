# Dynamic Program Creation Guide

The `program:create` command is now **fully dynamic** and can create any type of program!

## 🚀 Quick Examples

### 1. Create KYS 2027 by Cloning from KYS 2026
```bash
# Clone from KYS 2026 (ID: 9)
php spark program:create \
  --clone-from=9 \
  --name="Korea Youth Summit 2027"

# Or clone from KYS 2026 Batch 2 (ID: 18)
php spark program:create \
  --clone-from=18 \
  --name="Korea Youth Summit 2027"
```

### 2. Create KYS 2027 Batch 2 by Cloning
```bash
php spark program:create \
  --clone-from=9 \
  --name="Korea Youth Summit 2027" \
  --batch=2
```

### 3. Clone with Custom Dates
```bash
php spark program:create \
  --clone-from=9 \
  --name="Korea Youth Summit 2027" \
  --start-date=2027-05-01 \
  --end-date=2027-05-05
```

### 4. Create from Scratch (No Clone)
```bash
php spark program:create \
  --template=korea \
  --name="Korea Youth Summit 2027"
```

### 5. Create Japan Youth Summit 2026 Batch 1
```bash
php spark program:create \
  --clone-from=11 \
  --name="Japan Youth Summit 2026" \
  --batch=1
```

---

## 📋 How It Works

### With `--clone-from` (Recommended for New Years)
When you use `--clone-from=SOURCE_ID`, the command will:
1. ✅ Create new program record
2. ✅ Clone **Payments** (with periods)
3. ✅ Clone **Schedules** (dates shifted to new year)
4. ✅ Clone **Essay Questions**
5. ✅ Clone **Subthemes**
6. ✅ Clone **Documents**
7. ✅ Clone **Awards**
8. ✅ Clone **FAQs**
9. ✅ Clone **Speakers**
10. ✅ Clone **Rundowns** (dates shifted to new year)

### Without `--clone-from` (Create from Scratch)
Creates default/template data:
1. ✅ Create program record
2. ✅ Create default **Payments** (self/fully funded)
3. ✅ Create default **Schedules** (4 phases)
4. ✅ Create default **Essay Questions** (4 questions)
5. ❌ No subthemes, documents, awards, FAQs, speakers, rundowns

---

## 📋 Available Options

| Option | Description | Default |
|--------|-------------|---------|
| `--name` | **Program name** (e.g., "Japan Youth Summit 2026") | **Required** |
| `--clone-from` | **Source program ID** to clone all data from | None (create from scratch) |
| `--type` | Program type | `Summit` |
| `--category` | Category name | Same as program name |
| `--category-id` | Use existing category ID | Create new |
| `--year` | Program year | Current year |
| `--batch` | Batch number (optional) | None |
| `--start-date` | Start date (YYYY-MM-DD) | `{year}-06-01` or from clone source |
| `--end-date` | End date (YYYY-MM-DD) | `{year}-06-07` or from clone source |
| `--location` | Program location | From template/clone or Jakarta |
| `--self-funded` | Self funded amount (IDR) | From clone or 15M |
| `--fully-funded` | Fully funded fee (IDR) | From clone or 500K |
| `--web-url` | Custom web URL slug | Auto-generated |
| `--instagram` | Instagram handle | From template/clone |
| `--skip-payments` | Skip payment creation | Include payments |
| `--template` | Use preset: korea, japan, istanbul, default | None |

---

## 🎨 Available Templates (for Create from Scratch)

| Template | Location | Self Funded | Fully Funded | Instagram |
|----------|----------|-------------|--------------|-----------|
| `korea` | Seoul, South Korea | IDR 15M | IDR 500K | @koreayouthsummit |
| `japan` | Tokyo, Japan | IDR 18M | IDR 750K | @japanyouthsummitofficial |
| `istanbul` | Istanbul, Turkey | IDR 14M | IDR 500K | @istanbulyouthsummit |
| `default` | Jakarta, Indonesia | IDR 12M | IDR 500K | @ybbfoundation |

---

## 🔍 Finding Source Program IDs

To clone from an existing program, first find its ID:

```bash
php spark program:list
```

Example output:
```
🟢 Category: Korea Youth Summit
   Programs:
     🔴 Korea Youth Summit 2025 (ID: 5)
     🟢 Korea Youth Summit 2026 (ID: 9)   <-- Use this ID
     🔴 Korea Youth Summit 2026 Batch 2 (ID: 18)
```

---

## 💡 Best Practices

### Creating New Year (e.g., KYS 2027)
```bash
# Best: Clone from most recent similar program
php spark program:create --clone-from=9 --name="Korea Youth Summit 2027"

# Then customize dates if needed
php spark program:create \
  --clone-from=9 \
  --name="Korea Youth Summit 2027" \
  --start-date=2027-07-01 \
  --end-date=2027-07-05
```

### Creating New Batch (e.g., Batch 3)
```bash
# Clone from existing batch and change batch number
php spark program:create \
  --clone-from=18 \
  --name="Korea Youth Summit 2026" \
  --batch=3
```

### Creating Completely New Program
```bash
# Use template or interactive mode
php spark program:create --template=default --name="New Program 2026"

# Or interactive
php spark program:create
```

---

## ⚠️ Important Notes

1. **Cloning copies EVERYTHING** - Payments, schedules, essays, speakers, FAQs, etc.
2. **Dates are shifted** to the new year automatically
3. **Payments start INACTIVE** - Review and activate when ready
4. **Program starts INACTIVE** - Activate via admin panel
5. **Names must be unique** - Can't create duplicate program names

---

## 🐛 Troubleshooting

### "Source program not found"
Check the ID with: `php spark program:list`

### "Transaction failed"
Program name may already exist. Check existing programs.

### Slow response
Remote database connection can be slow. Consider using direct SQL for bulk operations.
