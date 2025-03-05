

### Install PM2 for the printer
` npm install -g pm2`

`npm install pm2-windows-startup -g`

`pm2 start printer-run.py --name binara-printer-app --interpreter python`

 The `printer-run.py` file

```{python}
import uvicorn

if __name__ == "__main__":
	uvicorn.run("app:app", host="0.0.0.0", port=5000)
```

`pm2 save`

```sudo chown -R www-data:www-data /var/www/api.binara.live/storage/```

```sudo chown -R www-data:www-data /var/www/api.binara.live/bootstrap/```
