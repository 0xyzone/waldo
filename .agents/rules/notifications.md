# Notifications Rule

## Task Completion & Error Notifications

At the end of every workflow, task completion, or upon encounter of unrecoverable errors:
1. Always invoke the Discord notification helper:
   ```bash
   php scripts/notify_discord.php <success|failure> "Summary of changes or error details"
   ```
2. When success:
   - Use status `success`.
   - Provide concise summary of what was completed/fixed.
3. When failure or critical error:
   - Use status `failure`.
   - Provide concise error description.
