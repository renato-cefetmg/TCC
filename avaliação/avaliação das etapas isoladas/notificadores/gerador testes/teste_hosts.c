#define FILEPATH "/usr/local/nagios/etc/utils/nagios_send_host.php"
#include <stdio.h>

int main() {
    FILE* fp;
    int i,j,k;
    fp = fopen("testes_host.sh","w+");
    fprintf(fp,"# !/bin/bash\n");
    for(i=1;i<=2;i++){
        for(j=1;j<=3;j++){
	    	for(k=1;k<=10;k++){
				fprintf(fp,"/usr/bin/php %s ",FILEPATH);
				fprintf(fp,"PAYLOAD%d ",k+10*(j-1)+30*(i-1));
				fprintf(fp,"device ");
				switch(j){
				   case 1: fprintf(fp, "UP "); break;
				   case 2: fprintf(fp, "DOWN "); break;
				   case 3: fprintf(fp, "UNREACHABLE "); break;   
				}
				switch(i){
				   case 1: fprintf(fp, "SOFT "); break;
				   case 2: fprintf(fp, "HARD "); break;   
				}
				fprintf(fp, "%i\n",k);
	   		}
		}
    }
    fclose(fp);
}
