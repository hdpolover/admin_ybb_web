# Scoring pages: funding-category column + filter

**Date:** 2026-06-15
**Repo:** `admin_ybb_web` (CodeIgniter 4)
**Status:** Approved, ready to implement

## Goal

On both **Scoring → Fully Funded** and **Scoring → Interview**, show each
participant's funding category (`fully_funded` / `self_funded`) as a table column
and let admins toggle which categories are shown. Default behavior is unchanged
(both pages show **Fully Funded** only until an admin changes the filter).

## Background

- `participants.category` is a plain varchar with exactly two values:
  `fully_funded`, `self_funded`. Set at registration, switchable via existing API.
- Both scoring tables are server-side DataTables (AJAX).
- Today both `Scorings::getData()` (Fully Funded) and `Scorings::getData2()`
  (Interview) **hardcode** `$category = 'fully_funded'`, so each page only ever
  lists fully-funded participants.
- A `getCategoryBadge()` helper already exists in `Scorings.php` (green "Fully
  Funded" / amber "Self Funded" badge).
- The Interview view already has a commented-out filter block, and its AJAX
  `data` function already sends `d.category` — currently a no-op because the DOM
  element doesn't exist.

## Changes

### 1. Server filter — `app/Controllers/Scorings.php`

In **both** `getData()` (~line 140) and `getData2()` (~line 270), replace the
hardcoded category with a request-driven value that defaults to `fully_funded`:

```php
$category = $this->request->getGet('category') ?: 'fully_funded';
if ($category !== 'all') {
    $builder->where('participants.category', $category);
}
```

- Missing/empty → `fully_funded` (preserves current behavior).
- `all` → no category filter (shows both).

Ensure the query SELECT includes `participants.category` (needed for the badge).

### 2. Category column — controller row data + both views

- Controller: add `'category' => $this->getCategoryBadge($row['category'])` to the
  `$data[]` row arrays in `getData()` and `getData2()`.
- Views (`scorings/fully_funded/index.php`, `scorings/interview/index.php`):
  add a `Category` `<th>` immediately after **Participant Details**, and a
  matching `{ data: 'category' }` entry at the same index in the JS `columns`
  array.

### 3. Filter dropdown — both views

Add a `Category` select following the existing pattern in
`users/participants/index.php`:

```html
<select id="filter-category" class="form-select">
    <option value="fully_funded" selected>Fully Funded</option>
    <option value="self_funded">Self Funded</option>
    <option value="all">All Categories</option>
</select>
```

- Default selection = **Fully Funded**, so the UI matches the data shown.
- On `change`, call `participantsTable.ajax.reload()`.
- Ensure the AJAX `data` function sends `d.category = $('#filter-category').val()`
  (already present in the Interview view; add to Fully Funded view).
- Interview view: uncomment/adapt the existing filter block. Fully Funded view:
  add the dropdown in the same toolbar area as the existing date-range filter.

## Out of scope

- **Export Data**: posts to `users/participants/export`; the `Scorings::export()`
  Excel path is an unimplemented stub. Not touched here.
- **Export tool** (`ybb-export-tool`, separate repo): it already exports the
  category column ("Category (Funded/Self)"). A funding-category *filter* there is
  a separate follow-up change, tracked independently.

## Verification

- Fully Funded page: loads showing only fully-funded (unchanged); switching the
  filter to Self Funded / All updates the table; Category badge renders per row.
- Interview page: same.
- Confirm `participants.category` appears in the SELECT so badges aren't blank.
