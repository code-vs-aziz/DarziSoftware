# Free AI for Report Voice Assistant

## Level 1 — Already works (no install)

- **Browser mic** (Chrome) → Web Speech API (free)
- **Keyword rules** in PHP — Urdu/Roman commands like `pichle 7 din cash ledger`

Go to **Reports** page → top panel **Voice / Text Report Assistant**.

## Level 2 — Optional local AI (Ollama — 100% free)

1. Install Ollama: https://ollama.com/download
2. Pull a small model:
   ```bash
   ollama pull llama3.2
   ```
3. In `.env`:
   ```
   OLLAMA_ENABLED=true
   OLLAMA_URL=http://127.0.0.1:11434
   OLLAMA_MODEL=llama3.2
   ```
4. Keep Ollama running in background while using voice commands.

When rules don't match, system asks Ollama to parse your sentence into report + dates.

## Example voice / text commands

| Command | Report |
|---------|--------|
| pichle 7 din cash ledger | Cash Ledger last 7 days |
| aaj delivery report | Today's deliveries |
| is mahine expense | Expense this month |
| pending payment 30 din | Pending payments |

## Notes

- Mic works best in **Google Chrome**
- Employee / Customer ledger opens **selection form** (pick employee/customer there)
- No OpenAI / paid API required
