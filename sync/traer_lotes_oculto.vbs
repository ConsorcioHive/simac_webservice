' traer_lotes_oculto.vbs — Ejecuta traer_lotes.bat sin ventana visible.
' Lo usa la tarea Windows SIMAC_TraerLotes (cada 2 min).
Set sh = CreateObject("WScript.Shell")
sh.Run "cmd /c ""C:\xampp\htdocs\simac_webservice\sync\traer_lotes.bat""", 0, False
