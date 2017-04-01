Ma premiere étape a été de séparer l'initialisation des variables internes a la classe et le travail de la classe elle-même

Ensuite, j'ai harmonisé l'ecriture des differents ifs / ternaires que j'ai pu voir

Au final, j'ai realisé que dans la plupart des cas, l'action etait la même :
 -trouver un tag
 -le remplacer par la valeur reelle qu'on va chercher ailleurs

Pour cela, j'ai mis en place un pointeur sur methode, ce qui permet:
- d'eviter les multiples assignations de variables de $text en passant par une reference
- d'isoler le remplacement de texte dans une fonction et le tableau replacements, cela sera plus simple d'ajouter ou de retirer un tag
- de separer la partie "logique" (chercher un tag, si il est trouve, faire ceci, sinon, faire cela) de la partie "concrete" (ce tag, je le remplace par ça)

Il y a des tags non utilises que j'ai pense a mettre entre commentaires, mais apres tout, peut-être d'autres mails sur lesquels je ne travaille pas les utilise, donc j'ai prefere les garder

