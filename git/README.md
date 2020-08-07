# Git

* [Modifier un commit précédent](#fixup)

## <a name="fixup"></a> Modifier un commit précédent - `git commit --fixup`

* `git commit --fixup eadd44668a7a562f4bd784138e145d354ccfa439`

--> La commande ci-dessus crée un commit de fixup

* `git rebase -i --autosquash <initial-base-commit-id>`
* `git push` ou `git push --force`

## Restaurer une branche dans un état correct après un `git push --force` erroné

* `git reflog`
* Repérer le hash de commit de l'état correct 
* `git reset --hard [hash]`
