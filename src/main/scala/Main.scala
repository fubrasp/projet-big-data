import org.apache.spark.{SparkConf, SparkContext}
import java.text.DateFormatSymbols
import java.text.SimpleDateFormat

import org.apache.spark.rdd.RDD

object Main {

  def main(args: Array[String]) {

    def getMonth(month:Int) : String = {
      return new DateFormatSymbols().getMonths()(month-1)
    }

    def getHrInterval(time:String): Int = {
      println("TIME"+time)
      val start = time.split(":")(0)
      return (start+"-"+start+1+" "+time.split(" ")(1)).toInt
    }

    def convertHourNormalFormat(date:String): String = {
      val displayFormat = new SimpleDateFormat("HH")
      val parseFormat = new SimpleDateFormat("MM/dd/YYYY hh:mm:ss a")
      return displayFormat.format(parseFormat.parse(date))
    }

    def getInterval(hour:Int): String = {
      val i04 = "0-4"
      val i48 = "4-8"
      val i812 = "8-12"
      val i1216 = "12-16"
      val i1620 = "16-20"
      val i2024 = "20-00"
      val m = hour match {
        case x if (x >= 0 && x <= 3) => i04
        case y if (y >= 4 && y < 8)  => i48
        case z if (z >= 8 && z < 12) => i812
        case a if (a >= 12 && a < 16) => i1216
        case b if (b >= 16 && b < 20) => i1620
        case c if (c >= 20 && c <= 23) => i2024
        case _ => "ERROR"
      }
      return m
    }

    val sc = getSparkContext()
    //Q1
    val debut1 = System.currentTimeMillis();
    sc.textFile("crimes.csv")
      .map(line => {
        val splitedLine = line.split(",")
        (splitedLine(5))
      })
      .filter(t => t.nonEmpty)
      .map(t => (t, 1))
      .reduceByKey(_ + _)
      .sortBy(sortLine => (- sortLine._2))
      .saveAsTextFile("q1")

    "Q1: "+println(System.currentTimeMillis - debut1)+" ms"

    //Q2
    val debut2 = System.currentTimeMillis();
    val data = sc.textFile("crimes.csv")
    val header = data.first()
    val dataWithoutHeader = data.filter(row => row != header)
    .map(line => line.split(","))
    .map(line=> convertHourNormalFormat(line(2)))
      .map(t => (getInterval(t.toInt), 1))
      .reduceByKey(_ + _)
      .saveAsTextFile("q2")
    "Q2: "+println(System.currentTimeMillis - debut1)+" ms"

    //Q3
    val debut3 = System.currentTimeMillis();
    // On calcule les clusters pour k = 10
    val kmeansObject = new KmeansClass()
    val finalAffectation = kmeansObject.kmeansFunction(sc)

    //prendre les 3 clusters les plus dangereux
    val troisClustersDangereux = finalAffectation
      ._3
      .sortBy(sortLine => (- sortLine._2))
      .take(3)

    sc.parallelize(troisClustersDangereux)
      .map(line => {
        (finalAffectation._1(line._1))
      })
      .saveAsTextFile("q3-plus-dangereux")

    //prendre les 3 clusters les moins dangereux
    val troisClustersMoinsDangereux = finalAffectation
      ._3
      .sortBy(sortLine => (sortLine._2))
      .take(3)

    sc.parallelize(troisClustersMoinsDangereux)
      .map(line => {
        (finalAffectation._1(line._1))
      })
      .saveAsTextFile("q3-moins-dangereux")
    "Q3: "+println(System.currentTimeMillis - debut1)+" ms"

    //Q4
    val debut4 = System.currentTimeMillis();
    sc.textFile("crimes.csv")
      .map(line => {
        val splitedLine = line.split(",")
        val splittedArrayLength = splitedLine.length
        (splitedLine(splittedArrayLength-15),splitedLine(splittedArrayLength-4),splitedLine(splittedArrayLength-3))
      })
        .filter(t => (t._1.equals("true")||t._1.equals("false")))
        .map(t => (t, 1))
        .reduceByKey(_ + _)
    .saveAsTextFile("q4")
    "Q4: "+println(System.currentTimeMillis - debut1)+" ms"

    //Q5
    val debut5 = System.currentTimeMillis();
    val q5 = sc.textFile("crimes.csv")
      .map(line => line.split(","))
      .map(line=> (line(2)))
      .map(line => line.split(" "))
      .map(line=>(line(0)))
      .map(line => line.split("/"))
      .map(line=>(line(0),1))
      .reduceByKey(_+_)
      .sortBy(_._2,false)
      .take(3)

      sc.parallelize(q5)
        .map(line => (getMonth(line._1.toInt),line._2))
        .saveAsTextFile("q5")

    "Q5: "+println(System.currentTimeMillis - debut1)+" ms"

  }

  def getSparkContext(): SparkContext = {
    val conf = new SparkConf()
      .setAppName("test")
      .setMaster("local[*]")
    val sc = new SparkContext(conf)
    sc.setLogLevel("ERROR")
    sc
  }
}

class Observation(var forme: String, var duree: Double) {

  override def toString = s"Observation($forme, $duree)"
}
