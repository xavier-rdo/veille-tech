# Composer

## Installer une dépendance locale (filesystem)

Référence : https://getcomposer.org/doc/05-repositories.md#path

Fichier `composer.json` : 

```diff
{
-    "minimum-stability": "stable",
+    "minimum-stability": "dev",
+    "repositories": [
+        {
+            "type": "path",
+            "url": "../MyOrgMyBundle"
+        }
+    ],
    "require": {
+        "my-org/my-bundle": "dev-master",
    }
}
```

Puis `composer require my-org/my-bundle`